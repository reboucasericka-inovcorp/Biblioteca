<?php

namespace App\Exports;

use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BooksExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        protected ?string $search = null,
        protected string $sort = 'name',
        protected string $dir = 'asc'
    ) {}

    public function query(): Builder
    {
        $query = Book::with(['publisher', 'authors']);

        if ($this->search) {
            $query->where(function (Builder $builder) {
                $builder->where('name', 'like', "%{$this->search}%")
                    ->orWhere('isbn', 'like', "%{$this->search}%");
            });
        }

        $query->orderBy($this->sort, $this->dir);

        return $query;
    }

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
