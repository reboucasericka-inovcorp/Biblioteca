<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AuthorController extends Controller
{
    public function index()
    {
        return view('authors.index');
    }

    public function create()
    {
        return view('authors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('authors', 'public');
        }

        Author::create([
            'name' => $request->name,
            'photo' => $photoPath,
        ]);

        return redirect()->route('authors.index')
            ->with('flash.banner', 'Author created successfully.')
            ->with('flash.bannerStyle', 'success');
    }

    public function edit(Author $author)
    {
        return view('authors.edit', compact('author'));
    }

    public function update(Request $request, Author $author)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
        ]);

        $data = ['name' => $request->name];

        if ($request->hasFile('photo')) {
            $rawPhoto = $author->getRawOriginal('photo');
            if ($rawPhoto) {
                Storage::disk('public')->delete($rawPhoto);
            }
            $data['photo'] = $request->file('photo')->store('authors', 'public');
        }

        $author->update($data);

        return redirect()->route('authors.index')
            ->with('flash.banner', 'Author updated successfully.')
            ->with('flash.bannerStyle', 'success');
    }

    public function destroy(Author $author)
    {
        $author->books()->detach();
        $rawPhoto = $author->getRawOriginal('photo');
        if ($rawPhoto) {
            Storage::disk('public')->delete($rawPhoto);
        }
        $author->delete();

        return redirect()->route('authors.index')
            ->with('flash.banner', 'Autor eliminado com sucesso.')
            ->with('flash.bannerStyle', 'success');
    }
}
