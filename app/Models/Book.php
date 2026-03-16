<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $table = 'books';

    protected $fillable = [
        'name',
        'isbn',
        'isbn_13',
        'price',
        'discount',
        'stock',
        'reserved_stock',
        'is_active',
        'publisher_id',
        'bibliography',
        'pages',
        'language',
        'dimensions',
        'cover',
        'google_volume_id',
        'thumbnail_url',
        'published_date',
        'file_path',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Criptografar campos sensíveis
    protected function bibliography(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (! $value) {
                    return null;
                }
                try {
                    return decrypt($value);
                } catch (\Exception $e) {
                    // Se falhar ao descriptografar, retorna o valor original (dados antigos)
                    return $value;
                }
            },
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class);
    }

    public function requisitions()
    {
        return $this->hasMany(Requisition::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function availabilityAlerts()
    {
        return $this->hasMany(BookAvailabilityAlert::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'book_category');
    }

    public function isAvailable(): bool
    {
        return ! $this->requisitions()
            ->whereIn('status', [
                Requisition::STATUS_ACTIVE,
                Requisition::STATUS_LATE,
            ])
            ->exists();
    }

    /**
     * Stock disponível para venda (stock físico menos reservas temporárias de checkout).
     */
    public function getAvailableStockAttribute(): int
    {
        $stock = (int) ($this->attributes['stock'] ?? 0);
        $reserved = (int) ($this->attributes['reserved_stock'] ?? 0);

        return max(0, $stock - $reserved);
    }
}
