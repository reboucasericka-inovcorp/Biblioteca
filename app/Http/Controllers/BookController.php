<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Publisher;
use App\Exports\BooksExport;
use Illuminate\Http\Request;
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
        return view('books.create', compact('publishers'));
    }

    public function show(Book $book)
    {
        return view('books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $publishers = Publisher::orderBy('name')->get();
        return view('books.edit', compact('book', 'publishers'));
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
            'price' => 'required|numeric',
            'publisher_id' => 'required|exists:publishers,id',
            'bibliography' => 'nullable|string',
            'cover' => 'nullable|image|max:2048',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('covers', 'public');
        }

        Book::create([
            'name' => $request->name,
            'isbn' => $request->isbn,
            'price' => $request->price,
            'publisher_id' => $request->publisher_id,
            'bibliography' => $request->bibliography,
            'cover' => $coverPath,
        ]);

        return redirect()->route('books.index')
            ->with('flash.banner', 'Book created successfully.')
            ->with('flash.bannerStyle', 'success');
    }

    public function update(Request $request, Book $book)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn,' . $book->id,
            'price' => 'required|numeric',
            'publisher_id' => 'required|exists:publishers,id',
            'bibliography' => 'nullable|string',
            'cover' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['name', 'isbn', 'price', 'publisher_id', 'bibliography']);

        if ($request->hasFile('cover')) {
            if ($book->cover) {
                Storage::disk('public')->delete($book->cover);
            }
            $data['cover'] = $request->file('cover')->store('covers', 'public');
        }

        $book->update($data);

        return redirect()->route('books.index')
            ->with('flash.banner', 'Book updated successfully.')
            ->with('flash.bannerStyle', 'success');
    }

    public function destroy(Book $book)
    {
        if ($book->cover) {
            Storage::disk('public')->delete($book->cover);
        }
        $book->delete();

        return redirect()->route('books.index')
            ->with('flash.banner', 'Book deleted successfully.')
            ->with('flash.bannerStyle', 'success');
    }
}
