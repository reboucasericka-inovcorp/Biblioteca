<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_LATE = 'late';

    protected $fillable = [
        'sequential_number',
        'user_id',
        'book_id',
        'request_date',
        'due_date',
        'return_date',
        'status',
        'days_elapsed',
        'photo_path'
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'due_date' => 'datetime',
        'return_date' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Automatic Sequential Generator
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($requisition) {

            $year = now()->year;

            $last = self::whereYear('request_date', $year)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (!$last) {
                $number = 1;
            } else {
                $lastNumber = (int) substr($last->sequential_number, -4);
                $number = $lastNumber + 1;
            }

            $requisition->sequential_number = sprintf(
                'RQ-%s-%04d',
                $year,
                $number
            );

            $requisition->request_date = now();
            $requisition->due_date = now()->addDays(5);
            $requisition->status = self::STATUS_ACTIVE;
        });
    }
}