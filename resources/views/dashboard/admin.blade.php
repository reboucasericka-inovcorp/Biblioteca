<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            Bem vindo {{ auth()->user()->name }}
        </h2>
    </x-slot>
    <div id="app">
        <div class="py-3 space-y-6">
            <admin-suggestions />
            <google-books-search :user-is-admin="@json(Auth::user()->hasRole('Admin'))" />
        </div>
    </div>
</x-app-layout>
