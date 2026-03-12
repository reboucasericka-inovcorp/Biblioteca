<?php

namespace App\Models;

use App\Models\Book;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'email',
        'total',
        'status',
        'stripe_session_id',
        'shipping_address',
        'shipping_city',
        'shipping_postal_code',
        'shipping_country',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    protected $appends = ['customer_name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function events()
    {
        return $this->hasMany(OrderEvent::class)->orderBy('created_at');
    }

    public function getCustomerNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }

        return $this->email ?? '—';
    }

    /**
     * Devolve o stock reservado pelos itens desta ordem (checkout expirado/cancelado).
     */
    public function releaseReservedStock(): void
    {
        foreach ($this->items as $item) {
            Book::query()
                ->where('id', $item->book_id)
                ->where('reserved_stock', '>=', $item->quantity)
                ->decrement('reserved_stock', $item->quantity);
        }
    }
}
