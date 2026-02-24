<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-night-blue leading-tight">
            Books
        </h2>
    </x-slot>
    <div id="app">
        <div class="py-3">
            <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8 w-full">
                <div class="bg-white p-6 rounded shadow border border-steel-gray/50">
                    <p class="mb-8">Books Menu</p>
                    {{-- Vue entra aqui --}}
                    <div class="mb-8">
                        <google-books-search :user-is-admin="@json(Auth::user()->hasRole('Admin'))"></google-books-search>
                    </div>
                    

                    <books-table :user-is-admin="@json(Auth::user()->hasRole('Admin'))"></books-table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>