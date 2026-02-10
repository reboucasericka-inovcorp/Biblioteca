<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Authors
        </h2>
    </x-slot>
    <div id="app">
        <div class="py-3">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-4">
                <div class="bg-white p-6 rounded shadow">

                    <p class="mb-4">Authors Menu</p>
                    <authors-table></authors-table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
