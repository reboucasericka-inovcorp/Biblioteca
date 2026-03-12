<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            Pedidos
        </h2>
    </x-slot>
    <div class="space-y-6">
        <div class="card shadow bg-base-100">
            <div class="card-body p-6">
                <orders-table />
            </div>
        </div>
    </div>
</x-admin-layout>
