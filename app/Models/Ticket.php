<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $fillable = [
        'service_id', 'counter_id', 'number', 'code', 'status',
        'queue_date', 'called_at', 'served_at', 'finished_at',
    ];

    protected $casts = [
        'queue_date'  => 'date',
        'called_at'   => 'datetime',
        'served_at'   => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }

    /**
     * Buat tiket baru untuk sebuah layanan (nomor urut harian).
     */
    public static function issueFor(Service $service): self
    {
        $date = now()->toDateString();

        $last = static::where('service_id', $service->id)
            ->whereDate('queue_date', $date)
            ->max('number');

        $number = ($last ?? 0) + 1;

        return static::create([
            'service_id' => $service->id,
            'number'     => $number,
            'code'       => $service->prefix . str_pad($number, 3, '0', STR_PAD_LEFT),
            'status'     => 'waiting',
            'queue_date' => $date,
        ]);
    }
}
