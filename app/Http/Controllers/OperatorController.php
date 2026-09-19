<?php

namespace App\Http\Controllers;

use App\Events\QueueUpdated;
use App\Events\TicketCalled;
use App\Models\Counter;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OperatorController extends Controller
{
    /** Halaman pemilihan loket. */
    public function index()
    {
        // Bebaskan loket yang operatornya sudah idle/menghilang.
        Counter::releaseStale();

        $userId = Auth::id();

        $counters = Counter::where('is_active', true)
            ->with('occupant')
            ->orderBy('sort_order')->orderBy('number')
            ->get();

        // Loket yang sedang ditempati operator saat ini (jika ada) untuk shortcut.
        $myCounter = $counters->firstWhere('occupied_by', $userId);

        return view('operator.index', compact('counters', 'myCounter', 'userId'));
    }

    /**
     * Klaim/pilih loket. Ditolak bila loket sedang ditempati operator lain.
     * Bila operator ini sudah menempati loket lain, loket lama dilepas dulu.
     */
    public function claim(Counter $counter)
    {
        $userId = Auth::id();

        if (! $counter->is_active) {
            return back()->withErrors(['loket' => 'Loket tidak aktif.']);
        }

        // Bebaskan loket basi dulu agar loket yang operatornya menghilang bisa diambil.
        Counter::releaseStale();

        $timeout = (int) config('antrian.loket_idle_timeout', 15);

        $ok = DB::transaction(function () use ($counter, $userId, $timeout) {
            // Kunci baris loket target agar tidak ada balapan (race condition).
            $fresh = Counter::whereKey($counter->id)->lockForUpdate()->first();

            // Occupant lain masih dianggap sah hanya bila belum melewati batas idle.
            $stillFresh = $fresh->occupied_at
                && $timeout > 0
                && $fresh->occupied_at->gt(now()->subMinutes($timeout));

            if ($fresh->occupied_by !== null && $fresh->occupied_by !== $userId && $stillFresh) {
                return false; // sudah dipakai operator lain yang masih aktif
            }

            // Lepas loket lain yang mungkin masih ditempati operator ini.
            Counter::where('occupied_by', $userId)
                ->where('id', '!=', $fresh->id)
                ->update(['occupied_by' => null, 'occupied_at' => null]);

            $fresh->update([
                'occupied_by' => $userId,
                'occupied_at' => now(),
            ]);

            return true;
        });

        if (! $ok) {
            return redirect()->route('operator.index')
                ->withErrors(['loket' => 'Loket tersebut sedang digunakan operator lain. Silakan pilih loket lain.']);
        }

        return redirect()->route('operator.show', $counter);
    }

    /** Lepaskan loket yang sedang ditempati operator ini. */
    public function release(Counter $counter)
    {
        if ($counter->occupied_by === Auth::id()) {
            $counter->update(['occupied_by' => null, 'occupied_at' => null]);
        }

        return redirect()->route('operator.index')
            ->with('status', 'Loket telah dilepas.');
    }

    /**
     * Heartbeat: panel operator memanggil ini secara berkala agar occupied_at
     * selalu segar. Jika loket sudah bukan milik operator ini (mis. sudah
     * di-auto-release lalu diambil orang lain), balas 409 supaya panel
     * kembali ke pemilihan loket.
     */
    public function heartbeat(Counter $counter)
    {
        if ($counter->occupied_by !== Auth::id()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Loket tidak lagi Anda tempati.',
            ], 409);
        }

        $counter->update(['occupied_at' => now()]);

        return response()->json(['ok' => true]);
    }

    /** Panel kerja satu loket. Hanya occupant yang boleh masuk. */
    public function show(Counter $counter)
    {
        if ($redirect = $this->guardOccupant($counter)) {
            return $redirect;
        }

        $counter->load('services');

        return view('operator.panel', compact('counter'));
    }

    /** Data ringkas untuk panel operator (dipakai saat load & fallback). */
    public function state(Counter $counter)
    {
        $date = now()->toDateString();

        $current = $counter->currentTicket();

        $waitingByService = $counter->services->map(function ($service) use ($date) {
            return [
                'service_id' => $service->id,
                'service'    => $service->name,
                'waiting'    => Ticket::where('service_id', $service->id)
                    ->whereDate('queue_date', $date)
                    ->where('status', 'waiting')
                    ->count(),
            ];
        });

        return response()->json([
            'counter'  => ['id' => $counter->id, 'name' => $counter->name, 'number' => $counter->number],
            'current'  => $current ? [
                'id' => $current->id, 'code' => $current->code, 'status' => $current->status,
            ] : null,
            'waiting'  => $waitingByService,
        ]);
    }

    /** Panggil nomor berikutnya (mengambil tiket 'waiting' terlama dari layanan loket ini). */
    public function callNext(Counter $counter)
    {
        if ($resp = $this->guardOccupantJson($counter)) {
            return $resp;
        }

        $counter->load('services');
        $serviceIds = $counter->services->pluck('id');
        $date = now()->toDateString();

        $ticket = DB::transaction(function () use ($serviceIds, $date, $counter) {
            $next = Ticket::whereIn('service_id', $serviceIds)
                ->whereDate('queue_date', $date)
                ->where('status', 'waiting')
                ->orderBy('number')
                ->lockForUpdate()
                ->first();

            if (! $next) {
                return null;
            }

            $next->update([
                'counter_id' => $counter->id,
                'status'     => 'called',
                'called_at'  => now(),
                'served_at'  => now(),
            ]);

            return $next;
        });

        if (! $ticket) {
            return response()->json(['message' => 'Tidak ada antrian menunggu.'], 404);
        }

        $this->safeBroadcast(new TicketCalled($ticket));
        $this->safeBroadcast(new QueueUpdated('called-next'));

        return response()->json(['code' => $ticket->code, 'id' => $ticket->id]);
    }

    /** Panggil ulang tiket yang sedang aktif (memutar suara lagi). */
    public function recall(Counter $counter)
    {
        if ($resp = $this->guardOccupantJson($counter)) {
            return $resp;
        }

        $ticket = $counter->currentTicket();

        if (! $ticket) {
            return response()->json(['message' => 'Tidak ada nomor aktif untuk dipanggil ulang.'], 404);
        }

        $ticket->update(['status' => 'called', 'called_at' => now()]);

        $this->safeBroadcast(new TicketCalled($ticket));

        return response()->json(['code' => $ticket->code, 'id' => $ticket->id]);
    }

    /** Tandai selesai dilayani. */
    public function finish(Counter $counter)
    {
        if ($resp = $this->guardOccupantJson($counter)) {
            return $resp;
        }

        $ticket = $counter->currentTicket();

        if (! $ticket) {
            return response()->json(['message' => 'Tidak ada nomor aktif.'], 404);
        }

        $ticket->update(['status' => 'done', 'finished_at' => now()]);

        $this->safeBroadcast(new QueueUpdated('finished'));

        return response()->json(['ok' => true]);
    }

    /** Lewati (skip) nomor aktif. */
    public function skip(Counter $counter)
    {
        if ($resp = $this->guardOccupantJson($counter)) {
            return $resp;
        }

        $ticket = $counter->currentTicket();

        if (! $ticket) {
            return response()->json(['message' => 'Tidak ada nomor aktif.'], 404);
        }

        $ticket->update(['status' => 'skipped', 'finished_at' => now()]);

        $this->safeBroadcast(new QueueUpdated('skipped'));

        return response()->json(['ok' => true]);
    }

    /** Guard untuk request halaman: pastikan user adalah occupant loket. */
    protected function guardOccupant(Counter $counter)
    {
        if ($counter->occupied_by !== Auth::id()) {
            return redirect()->route('operator.index')
                ->withErrors(['loket' => 'Anda belum memilih loket ini atau loket sedang dipakai operator lain.']);
        }

        return null;
    }

    /** Guard untuk request AJAX/JSON: pastikan user adalah occupant loket. */
    protected function guardOccupantJson(Counter $counter)
    {
        if ($counter->occupied_by !== Auth::id()) {
            return response()->json([
                'message' => 'Sesi loket tidak valid. Loket sedang dipakai operator lain atau Anda belum memilihnya.',
            ], 403);
        }

        return null;
    }
}
