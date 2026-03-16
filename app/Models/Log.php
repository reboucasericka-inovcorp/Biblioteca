<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_date',
        'log_time',
        'user_id',
        'module',
        'object_id',
        'change',
        'ip',
        'browser',
    ];

    protected $casts = [
        'log_date' => 'date',
        'object_id' => 'integer',
    ];

    /**
     * log_time na BD é string "H:i:s"; expor como Carbon para format() nas views.
     */
    protected function logTime(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if (! $value) {
                    return null;
                }
                try {
                    return \Carbon\Carbon::parse($value);
                } catch (\Throwable) {
                    return null;
                }
            },
            set: fn ($value) => $value instanceof \DateTimeInterface ? $value->format('H:i:s') : $value,
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
