<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Author extends Model
{
    protected $table = 'authors';
    protected $fillable = ['name', 'photo'];

    protected function photo(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value
                ? (str_starts_with($value, 'http') ? $value : Storage::disk('public')->path($value))
                : null,
        );
    }

    public function books()
    {
        return $this->belongsToMany(Book::class);
    }
}