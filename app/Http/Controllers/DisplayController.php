<?php

namespace App\Http\Controllers;

use App\Models\Ticket;

class DisplayController extends Controller
{
    public function index()
    {
        return view('display.index');
    }

    /** Data untuk layar monitor: nomor aktif per loket + panggilan terakhir. */
    public function state()
    {
        $date = now()->toDateString();

        $active = Ticket::with(['counter', 'service'])
            ->whereDate('queue_date', $date)
            ->whereIn('status', ['called', 'serving'])
            ->whereNotNull('counter_id')
            ->orderByDesc('called_at')
            ->get()
            ->unique('counter_id')
            ->map(fn ($t) => [
                'code'           => $t->code,
                'service'        => $t->service?->name,
                'counter_name'   => $t->counter?->name,
                'counter_number' => $t->counter?->number,
                'called_at'      => optional($t->called_at)->toIso8601String(),
            ])->values();

        $last = Ticket::with(['counter', 'service'])
            ->whereDate('queue_date', $date)
            ->whereNotNull('called_at')
            ->orderByDesc('called_at')
            ->first();

        return response()->json([
            'active' => $active,
            'last'   => $last ? [
                'code'           => $last->code,
                'counter_name'   => $last->counter?->name,
                'counter_number' => $last->counter?->number,
            ] : null,
        ]);
    }
}
