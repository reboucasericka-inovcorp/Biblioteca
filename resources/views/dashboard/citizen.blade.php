<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-night-blue leading-tight shrink-0">
            Bem vindo {{ auth()->user()->name }}
        </h2>
    </x-slot>
    <div class="py-3">
        <citizen-dashboard />
    </div>
</x-app-layout>
