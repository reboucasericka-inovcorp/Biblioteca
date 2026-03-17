<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\Publisher;
use App\Exports\BooksExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class BookController extends Controller
{
    public function index()
    {
        return view('books.index');
    }

    public function create()
    {
        $publishers = Publisher::orderBy('name')->get();
        $authors = Author::orderBy('name')->get();
        return view('books.create', compact('publishers', 'authors'));
    }

    public function show(Book $book)
    {
        return view('books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $publishers = Publisher::orderBy('name')->get();
        $authors = Author::orderBy('name')->get();
        $book->load('authors');
        $uploadMax = ini_get('upload_max_filesize');
        $postMax = ini_get('post_max_size');
        return view('books.edit', compact('book', 'publishers', 'authors', 'uploadMax', 'postMax'));
    }

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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'publisher_id' => 'required|exists:publishers,id',
            'bibliography' => 'nullable|string',
            'cover' => 'nullable|image|max:2048',
            'file' => 'nullable|file|mimes:pdf|max:20480',
            'authors' => 'nullable|array',
            'authors.*' => 'exists:authors,id',
        ], $this->fileValidationMessages());

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('covers', 'public');
        }

        $filePath = null;
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            File::ensureDirectoryExists(Storage::disk('books')->path(''));
            $filePath = $request->file('file')->store('', 'books');
        }

        $book = Book::create([
            'name' => $request->name,
            'isbn' => $request->isbn,
            'price' => $request->price,
            'discount' => $request->input('discount', 0),
            'stock' => $request->input('stock', 0),
            'is_active' => $request->boolean('is_active'),
            'publisher_id' => $request->publisher_id,
            'bibliography' => $request->bibliography,
            'cover' => $coverPath,
            'file_path' => $filePath,
        ]);

        $book->authors()->sync($request->input('authors', []));

        return redirect()->route('books.index')
            ->with('flash.banner', 'Book created successfully.')
            ->with('flash.bannerStyle', 'success');
    }

    public function update(Request $request, Book $book)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn,' . $book->id,
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'publisher_id' => 'required|exists:publishers,id',
            'bibliography' => 'nullable|string',
            'cover' => 'nullable|image|max:2048',
            'file' => 'nullable|file|mimes:pdf|max:20480',
            'authors' => 'nullable|array',
            'authors.*' => 'exists:authors,id',
        ], $this->fileValidationMessages());

        $data = $request->only(['name', 'isbn', 'price', 'discount', 'stock', 'publisher_id', 'bibliography']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('cover')) {
            if ($book->cover) {
                Storage::disk('public')->delete($book->cover);
            }
            $data['cover'] = $request->file('cover')->store('covers', 'public');
        }

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            if ($book->file_path) {
                Storage::disk('books')->delete($book->file_path);
            }
            File::ensureDirectoryExists(Storage::disk('books')->path(''));
            $data['file_path'] = $request->file('file')->store('', 'books');
        }

        $book->update($data);
        $book->authors()->sync($request->input('authors', []));

        return redirect()->route('books.index')
            ->with('flash.banner', 'Book updated successfully.')
            ->with('flash.bannerStyle', 'success');
    }

    /**
     * Mensagens de validação para o ficheiro PDF (evitar "The file failed to upload" genérico).
     */
    private function fileValidationMessages(): array
    {
        return [
            'file.mimes' => 'O ficheiro do livro deve ser PDF.',
            'file.max' => 'O PDF não pode exceder 20 MB. Se o seu ficheiro for menor, aumente upload_max_filesize e post_max_size no PHP.',
            'file.uploaded' => 'O PDF não pôde ser enviado. Verifique o tamanho (máx. 20 MB) e as definições do servidor (upload_max_filesize, post_max_size).',
        ];
    }

    public function destroy(Book $book)
    {
        if ($book->requisitions()->exists()) {
            return redirect()->back()
                ->with('flash.banner', 'Não é possível eliminar um livro com histórico de requisições.')
                ->with('flash.bannerStyle', 'danger');
        }

        $book->authors()->detach();
        if ($book->cover) {
            Storage::disk('public')->delete($book->cover);
        }
        if ($book->file_path) {
            Storage::disk('books')->delete($book->file_path);
        }
        $book->delete();

        return redirect()->route('books.index')
            ->with('flash.banner', 'Livro eliminado com sucesso.')
            ->with('flash.bannerStyle', 'success');
    }
}
