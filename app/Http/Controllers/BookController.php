<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Exports\BooksExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BookController extends Controller
{
    public function index()
    {
        return view('books.index');
    }

    /**
     * Exportar livros para Excel (rota web, usa sessão auth).
     */
    public function export(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'name');
        $dir = $request->get('dir', 'asc');

        return Excel::download(
            new BooksExport($search, $sort, $dir),
            'books.xlsx'
        );
    }
}
