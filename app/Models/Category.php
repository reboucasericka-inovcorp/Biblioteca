<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_category');
    }
}
