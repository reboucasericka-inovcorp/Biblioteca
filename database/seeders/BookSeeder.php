<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publisher = Publisher::first();
        $authors = Author::all();

        $book = Book::create([
            'isbn' => '9780141036137',
            'name' => '1984',
            'bibliography' => 'Dystopian novel',
            'price' => 14.99,
            'publisher_id' => $publisher->id,
        ]);

        $book->authors()->attach(
            $authors->where('name', 'George Orwell')->pluck('id')
        );
    }
}