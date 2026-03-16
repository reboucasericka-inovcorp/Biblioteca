<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Author extends Model
{
    use HasFactory;

    protected $table = 'authors';
    protected $fillable = ['name', 'photo'];
    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute(): ?string
    {
        $path = $this->getRawOriginal('photo');
        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function books()
    {
        return $this->belongsToMany(Book::class);
    }
}