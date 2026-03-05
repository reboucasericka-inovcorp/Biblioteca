<?php

namespace Tests\Unit;

use App\Exports\BooksExport;
use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BooksExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_usa_query_com_eager_loading_sem_materializar_em_memoria(): void
    {
        $export = new BooksExport('Clean', 'name', 'asc');
        $query = $export->query();

        $this->assertInstanceOf(Builder::class, $query);
        $this->assertSame(['publisher', 'authors'], array_keys($query->getEagerLoads()));
        $this->assertSame(Book::class, $query->getModel()::class);
    }
}
