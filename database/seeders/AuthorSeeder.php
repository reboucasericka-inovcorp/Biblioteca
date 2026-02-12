<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Author;

class AuthorSeeder extends Seeder
{
    public function run(): void
    {
        $authors = [
            'George Orwell',
            'J. K. Rowling',
            'Martin Fowler',
            'Agatha Christie',
            'Stephen King',
            'J.R.R. Tolkien',
            'Paulo Coelho',
            'Isaac Asimov',
            'Gabriel García Márquez',
            'Ernest Hemingway',
            'Jane Austen',
            'Charles Dickens',
            'Robert C. Martin',
            'Eric Evans',
            'Kent Beck',
        ];

        foreach ($authors as $name) {
            Author::firstOrCreate(['name' => $name]);
        }
    }
}