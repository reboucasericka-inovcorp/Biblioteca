<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-night-blue leading-tight">
            Bem vindo {{ auth()->user()->name }}
        </h2>
    </x-slot>
    <div id="app">
        <div class="py-3">
            <citizen-dashboard />
        </div>
    </div>
</x-app-layout>
