<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-night-blue leading-tight">
            Authors
        </h2>
    </x-slot>
    <div id="app">
        <div class="py-3">
            <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8 w-full">
                <div class="bg-white p-6 rounded shadow border border-steel-gray/50">

                    <p class="mb-4">Authors Menu</p>
                    <authors-table :user-is-admin="@json(Auth::user()->hasRole('Admin'))"></authors-table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
