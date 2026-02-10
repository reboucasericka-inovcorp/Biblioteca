<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Author;

class AuthorSeeder extends Seeder
{
    public function run(): void
    {
        
        Author::create(['name' => 'George Orwell']);
        Author::create(['name' => 'J. K. Rowling']);
        Author::create(['name' => 'Martin Fowler']);
    }
}