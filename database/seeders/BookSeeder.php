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
        $authors = Author::all()->keyBy('name');
        $publishers = Publisher::all()->keyBy('name');

        $books = [
            [
                'isbn' => '9780141036137',
                'name' => '1984',
                'bibliography' => 'Dystopian novel about totalitarianism and surveillance.',
                'price' => 14.99,
                'publisher' => 'Penguin Books',
                'authors' => ['George Orwell'],
            ],
            [
                'isbn' => '9780141036144',
                'name' => 'Animal Farm',
                'bibliography' => 'Political satire allegory about totalitarianism.',
                'price' => 12.99,
                'publisher' => 'Penguin Books',
                'authors' => ['George Orwell'],
            ],
            [
                'isbn' => '9780439139601',
                'name' => 'Harry Potter and the Philosopher\'s Stone',
                'bibliography' => 'First book in the Harry Potter series.',
                'price' => 15.99,
                'publisher' => 'HarperCollins',
                'authors' => ['J. K. Rowling'],
            ],
            [
                'isbn' => '9780439139595',
                'name' => 'Harry Potter and the Chamber of Secrets',
                'bibliography' => 'Second book in the Harry Potter series.',
                'price' => 15.99,
                'publisher' => 'HarperCollins',
                'authors' => ['J. K. Rowling'],
            ],
            [
                'isbn' => '9780132350884',
                'name' => 'Clean Code',
                'bibliography' => 'A Handbook of Agile Software Craftsmanship.',
                'price' => 49.99,
                'publisher' => 'Prentice Hall',
                'authors' => ['Robert C. Martin'],
            ],
            [
                'isbn' => '9780137081073',
                'name' => 'The Clean Coder',
                'bibliography' => 'A Code of Conduct for Professional Programmers.',
                'price' => 44.99,
                'publisher' => 'Prentice Hall',
                'authors' => ['Robert C. Martin'],
            ],
            [
                'isbn' => '9780321125217',
                'name' => 'Domain-Driven Design',
                'bibliography' => 'Tackling Complexity in the Heart of Software.',
                'price' => 54.99,
                'publisher' => 'Addison-Wesley',
                'authors' => ['Eric Evans'],
            ],
            [
                'isbn' => '9780135974445',
                'name' => 'Refactoring',
                'bibliography' => 'Improving the Design of Existing Code.',
                'price' => 47.99,
                'publisher' => 'Addison-Wesley',
                'authors' => ['Martin Fowler'],
            ],
            [
                'isbn' => '9780060554736',
                'name' => 'The Lord of the Rings',
                'bibliography' => 'Epic fantasy novel, the trilogy in one volume.',
                'price' => 29.99,
                'publisher' => 'HarperCollins',
                'authors' => ['J.R.R. Tolkien'],
            ],
            [
                'isbn' => '9780553382563',
                'name' => 'The Alchemist',
                'bibliography' => 'Novel about following your dreams.',
                'price' => 13.99,
                'publisher' => 'Editora Sextante',
                'authors' => ['Paulo Coelho'],
            ],
            [
                'isbn' => '9780553382564',
                'name' => 'One Hundred Years of Solitude',
                'bibliography' => 'Magical realism novel about the Buendía family.',
                'price' => 16.99,
                'publisher' => 'HarperCollins',
                'authors' => ['Gabriel García Márquez'],
            ],
            [
                'isbn' => '9780141036145',
                'name' => 'Murder on the Orient Express',
                'bibliography' => 'Detective Hercule Poirot investigates a murder.',
                'price' => 11.99,
                'publisher' => 'Penguin Books',
                'authors' => ['Agatha Christie'],
            ],
            [
                'isbn' => '9780141036146',
                'name' => 'The Shining',
                'bibliography' => 'Horror novel about a family in an isolated hotel.',
                'price' => 14.99,
                'publisher' => 'Penguin Books',
                'authors' => ['Stephen King'],
            ],
            [
                'isbn' => '9780553293357',
                'name' => 'Foundation',
                'bibliography' => 'Science fiction novel about the fall of galactic empire.',
                'price' => 14.99,
                'publisher' => 'HarperCollins',
                'authors' => ['Isaac Asimov'],
            ],
            [
                'isbn' => '9780141036147',
                'name' => 'Pride and Prejudice',
                'bibliography' => 'Classic romance novel about Elizabeth Bennet.',
                'price' => 9.99,
                'publisher' => 'Penguin Books',
                'authors' => ['Jane Austen'],
            ],
            [
                'isbn' => '9780141036148',
                'name' => 'Test Driven Development',
                'bibliography' => 'By Example - software development methodology.',
                'price' => 52.99,
                'publisher' => 'Addison-Wesley',
                'authors' => ['Kent Beck'],
            ],
        ];

        foreach ($books as $data) {
            $pub = $publishers->get($data['publisher']);
            if (!$pub) {
                continue;
            }

            $book = Book::firstOrCreate(
                ['isbn' => $data['isbn']],
                [
                    'name' => $data['name'],
                    'bibliography' => $data['bibliography'],
                    'price' => $data['price'],
                    'publisher_id' => $pub->id,
                ]
            );

            $authorIds = $authors->whereIn('name', $data['authors'])->pluck('id')->toArray();
            $book->authors()->syncWithoutDetaching($authorIds);
        }
    }
}
