<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Book Detail
        </h2>
    </x-slot>
    <div id="app">
        <book-detail :book-id="{{ $book->id }}"></book-detail>
    </div>
</x-app-layout>
