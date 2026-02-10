<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $table = 'authors';
    protected $fillable = ['nome', 'foto'];

    public function books()
    {
        return $this->belongsToMany(Book::class);
    }
}