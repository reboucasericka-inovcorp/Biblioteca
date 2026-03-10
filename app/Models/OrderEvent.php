<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderEvent extends Model
{
    public const EVENT_ORDER_CREATED = 'order_created';

    public const EVENT_PAYMENT_STARTED = 'payment_started';

    public const EVENT_PAYMENT_COMPLETED = 'payment_completed';

    public const EVENT_PAYMENT_EXPIRED = 'payment_expired';

    public const EVENT_ORDER_CANCELLED = 'order_cancelled';

    protected $fillable = [
        'order_id',
        'event',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
