<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = ['name', 'prefix', 'description', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function counters(): BelongsToMany
    {
        return $this->belongsToMany(Counter::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
