<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Book extends Model
{
    protected $table = 'books';
    
    protected $fillable = [
        'name',
        'isbn',
        'price',
        'publisher_id',
        'bibliography',
        'cover',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Criptografar campos sensíveis
    protected function bibliography(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) return null;
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
}
