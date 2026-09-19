<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Disiarkan setiap kali sebuah nomor antrian dipanggil / dipanggil ulang.
 * Layar Display & Operator mendengarkan channel publik "queue".
 */
class TicketCalled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Ticket $ticket)
    {
        $this->ticket->loadMissing(['service', 'counter']);
    }

    public function broadcastOn(): Channel
    {
        return new Channel('queue');
    }

    public function broadcastAs(): string
    {
        return 'ticket.called';
    }

    public function broadcastWith(): array
    {
        return [
            'id'            => $this->ticket->id,
            'code'          => $this->ticket->code,
            'number'        => $this->ticket->number,
            'status'        => $this->ticket->status,
            'service'       => $this->ticket->service?->name,
            'counter_id'    => $this->ticket->counter_id,
            'counter_name'  => $this->ticket->counter?->name,
            'counter_number'=> $this->ticket->counter?->number,
            // Payload untuk pemutaran suara di layar Display (urutan file audio).
            'audio'         => [
                'code_digits'    => str_split((string) $this->ticket->number),
                'code_prefix'    => $this->ticket->service?->prefix,
                'counter_number' => $this->ticket->counter?->number,
            ],
            'called_at'     => optional($this->ticket->called_at)->toIso8601String(),
        ];
    }
}
