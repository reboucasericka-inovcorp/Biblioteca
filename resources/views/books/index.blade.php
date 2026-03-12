<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                Livros
            </h2>
            <a href="{{ route('books.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md shadow hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                Criar livro
            </a>
        </div>
    </x-slot>
    <div class="space-y-6">
        <div class="card shadow bg-base-100">
            <div class="card-body p-6">
                <div class="mb-6">
                    <google-books-search :user-is-admin="@json(Auth::user()->hasRole('Admin'))"></google-books-search>
                </div>
                <books-table :user-is-admin="@json(Auth::user()->hasRole('Admin'))"></books-table>
            </div>
        </div>
    </div>
</x-admin-layout>
