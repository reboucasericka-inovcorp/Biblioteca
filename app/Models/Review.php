<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_REFUSED = 'refused';

    protected $fillable = [
        'user_id',
        'book_id',
        'requisition_id',
        'rating',
        'comment',
        'status',
        'refusal_reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }
}
