<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>
    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('books.index') }}" class="p-6 bg-white rounded shadow hover:bg-gray-50">
            📚 Books
        </a>

        <a href="{{ route('authors.index') }}" class="p-6 bg-white rounded shadow hover:bg-gray-50">
            ✍️ Authors
        </a>

        <a href="{{ route('publishers.index') }}" class="p-6 bg-white rounded shadow hover:bg-gray-50">
            🏢 Publishers
        </a>
    </div>
</x-app-layout>