<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-night-blue leading-tight">
            Publishers
        </h2>
    </x-slot>
    <div id="app">
        <div class="py-3">
            <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8 w-full">
                <div class="bg-white p-6 rounded shadow border border-steel-gray/50">
                    <p class="mb-4">Publishers Menu</p>
                    {{-- Vue entra aqui --}}
                    <publishers-table :user-is-admin="@json(Auth::user()->hasRole('Admin'))"></publishers-table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
