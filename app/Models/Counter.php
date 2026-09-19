<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Counter extends Model
{
    /**
     * Bebaskan semua loket yang operatornya sudah idle melebihi batas
     * (mis. browser ditutup / koneksi putus / logout paksa). Dipanggil
     * sebelum menampilkan daftar loket & sebelum klaim.
     *
     * @return int jumlah loket yang dibebaskan
     */
    public static function releaseStale(): int
    {
        $timeout = (int) config('antrian.loket_idle_timeout', 15);

        if ($timeout <= 0) {
            return 0; // 0/negatif = fitur auto-release dimatikan
        }

        return static::whereNotNull('occupied_by')
            ->where('occupied_at', '<', now()->subMinutes($timeout))
            ->update(['occupied_by' => null, 'occupied_at' => null]);
    }

    protected $fillable = ['name', 'number', 'is_active', 'sort_order', 'occupied_by', 'occupied_at'];

    protected $casts = [
        'is_active'   => 'boolean',
        'occupied_at' => 'datetime',
    ];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /** Operator yang sedang menempati loket ini (NULL = bebas). */
    public function occupant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'occupied_by');
    }

    /** Apakah loket sedang ditempati operator lain (bukan $userId). */
    public function isOccupiedByOther(?int $userId): bool
    {
        return $this->occupied_by !== null && $this->occupied_by !== $userId;
    }

    /**
     * Tiket yang sedang dilayani/dipanggil di loket ini hari ini.
     */
    public function currentTicket()
    {
        return $this->tickets()
            ->whereDate('queue_date', now()->toDateString())
            ->whereIn('status', ['called', 'serving'])
            ->latest('called_at')
            ->first();
    }
}
