<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            Requisições
        </h2>
    </x-slot>
    <div class="space-y-6">
        <div class="card shadow bg-base-100">
            <div class="card-body p-6">
                <requisitions-table :user-is-admin="@json(Auth::user()->hasRole('Admin'))"></requisitions-table>
            </div>
        </div>
    </div>
</x-admin-layout>
