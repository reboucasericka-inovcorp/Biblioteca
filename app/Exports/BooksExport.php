<?php

namespace App\Exports;

use App\Models\Book;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BooksExport implements FromCollection, WithHeadings, WithMapping
{
    protected $search;
    protected $sort;
    protected $dir;

    public function __construct($search = null, $sort = 'name', $dir = 'asc')
    {
        $this->search = $search;
        $this->sort = $sort;
        $this->dir = $dir;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Book::with(['publisher', 'authors']);

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%")
                  ->orWhere('isbn', 'like', "%{$this->search}%");
        }

        $query->orderBy($this->sort, $this->dir);

        return $query->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ISBN',
            'Name',
            'Publisher',
            'Authors',
            'Bibliography',
            'Price (€)',
        ];
    }

    /**
     * @param Book $book
     * @return array
     */
    public function map($book): array
    {
        return [
            $book->isbn,
            $book->name,
            $book->publisher->name ?? '-',
            $book->authors->pluck('name')->join(', ') ?: '-',
            $book->bibliography ?? '-',
            number_format($book->price, 2, '.', ','),
        ];
    }
}
