<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    protected $table = 'publishers';
    protected $fillable = ['nome', 'logotipo', 'notas'];

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
