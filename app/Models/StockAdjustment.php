<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    protected $fillable = [
        'stock_reconciliation_run_id',
        'book_id',
        'stock_before',
        'stock_after',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(StockReconciliationRun::class, 'stock_reconciliation_run_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
