<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Publisher extends Model
{
    protected $table = 'publishers';
    
    protected $fillable = ['name', 'logo', 'notes'];

    // Criptografar notas
    protected function notes(): Attribute
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

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
