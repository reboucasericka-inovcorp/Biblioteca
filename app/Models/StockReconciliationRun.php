<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockReconciliationRun extends Model
{
    protected $fillable = [
        'rolled_back_at',
    ];

    protected $casts = [
        'rolled_back_at' => 'datetime',
    ];

    public function adjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }
}
