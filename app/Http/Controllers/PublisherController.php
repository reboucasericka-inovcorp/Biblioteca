<?php

namespace App\Http\Controllers;

use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublisherController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()?->hasRole('Admin')) {
            return view('publishers.index');
        }

        return view('publishers.browse');
    }

    public function create()
    {
        return view('publishers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        Publisher::create([
            'name' => $request->name,
            'notes' => $request->notes,
            'logo' => $logoPath,
        ]);

        return redirect()->route('publishers.index')
            ->with('flash.banner', 'Publisher created successfully.')
            ->with('flash.bannerStyle', 'success');
    }

    public function edit(Publisher $publisher)
    {
        return view('publishers.edit', compact('publisher'));
    }

    public function update(Request $request, Publisher $publisher)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        $data = ['name' => $request->name, 'notes' => $request->notes];

        if ($request->hasFile('logo')) {
            if ($publisher->logo) {
                Storage::disk('public')->delete($publisher->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $publisher->update($data);

        return redirect()->route('publishers.index')
            ->with('flash.banner', 'Publisher updated successfully.')
            ->with('flash.bannerStyle', 'success');
    }

    public function destroy(Publisher $publisher)
    {
        $books = $publisher->books;
        foreach ($books as $book) {
            if ($book->requisitions()->exists()) {
                return redirect()->back()
                    ->with('flash.banner', 'Não é possível eliminar uma editora com livros que têm histórico de requisições.')
                    ->with('flash.bannerStyle', 'danger');
            }
        }
        foreach ($books as $book) {
            $book->authors()->detach();
            if ($book->cover) {
                Storage::disk('public')->delete($book->cover);
            }
            $book->delete();
        }
        if ($publisher->logo) {
            Storage::disk('public')->delete($publisher->logo);
        }
        $publisher->delete();

        return redirect()->route('publishers.index')
            ->with('flash.banner', 'Editora eliminada com sucesso.')
            ->with('flash.bannerStyle', 'success');
    }
}
