<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookSuggestion extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'google_volume_id',
        'title',
        'authors',
        'thumbnail_url',
        'status',
    ];

    protected $casts = [
        'authors' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
