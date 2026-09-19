<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Disiarkan saat komposisi antrian berubah (tiket baru diambil, diselesaikan, dilewati).
 * Dipakai layar untuk menyegarkan daftar tunggu tanpa memutar suara.
 */
class QueueUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public string $reason = 'updated')
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('queue');
    }

    public function broadcastAs(): string
    {
        return 'queue.updated';
    }

    public function broadcastWith(): array
    {
        return ['reason' => $this->reason, 'at' => now()->toIso8601String()];
    }
}
