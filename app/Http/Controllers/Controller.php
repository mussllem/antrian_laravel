<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Throwable;

abstract class Controller
{
    /**
     * Siarkan event realtime tanpa menggagalkan operasi inti.
     *
     * Bila server broadcast (mis. Laravel Reverb) sedang mati / tak terjangkau,
     * penyiaran memang gagal — tetapi ambil nomor / panggilan tetap berhasil.
     * Layar akan tetap tersinkron lewat polling fallback. Kegagalan hanya dicatat
     * sebagai peringatan di log, bukan dilempar sebagai error ke pengguna.
     */
    protected function safeBroadcast(object $event): void
    {
        try {
            broadcast($event);
        } catch (Throwable $e) {
            Log::warning('Broadcast gagal (diabaikan): '.$e->getMessage(), [
                'event' => $event::class,
            ]);
        }
    }
}
