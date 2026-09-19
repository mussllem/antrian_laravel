<?php

namespace App\Http\Controllers;

use App\Events\QueueUpdated;
use App\Models\Service;
use App\Models\Ticket;
use Illuminate\Http\Request;

class KioskController extends Controller
{
    public function index()
    {
        $services = Service::where('is_active', true)
            ->orderBy('sort_order')->get();

        return view('kiosk.index', compact('services'));
    }

    public function take(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
        ]);

        $service = Service::where('is_active', true)->findOrFail($data['service_id']);
        $ticket  = Ticket::issueFor($service);

        $this->safeBroadcast(new QueueUpdated('ticket-issued'));

        // Berapa banyak yang menunggu di depan tiket ini.
        $ahead = Ticket::where('service_id', $service->id)
            ->whereDate('queue_date', now()->toDateString())
            ->where('status', 'waiting')
            ->where('number', '<', $ticket->number)
            ->count();

        return response()->json([
            'id'        => $ticket->id,
            'code'      => $ticket->code,
            'number'    => $ticket->number,
            'service'   => $service->name,
            'ahead'     => $ahead,
            'print_url' => route('kiosk.print', $ticket),
        ]);
    }

    /** Halaman struk tiket siap cetak (ukuran termal 58/80mm). */
    public function print(Ticket $ticket)
    {
        $ticket->load('service');

        // Jumlah yang menunggu di depan pada saat cetak.
        $ahead = Ticket::where('service_id', $ticket->service_id)
            ->whereDate('queue_date', $ticket->queue_date)
            ->where('status', 'waiting')
            ->where('number', '<', $ticket->number)
            ->count();

        return view('kiosk.ticket', compact('ticket', 'ahead'));
    }
}
