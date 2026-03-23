<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DirectConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one_id',
        'user_two_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (DirectConversation $conversation) {
            if ($conversation->user_one_id > $conversation->user_two_id) {
                [$conversation->user_one_id, $conversation->user_two_id] = [
                    $conversation->user_two_id,
                    $conversation->user_one_id,
                ];
            }
        });
    }

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function messages(): MorphMany
    {
        return $this->morphMany(Message::class, 'messageable');
    }
}
