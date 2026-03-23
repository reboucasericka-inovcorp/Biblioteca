<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-night-blue leading-tight">
            Bem vindo {{ auth()->user()->name }}
        </h2>
    </x-slot>
    <meta name="user-id" content="{{ (int) auth()->id() }}">
    <meta name="user-name" content="{{ auth()->user()?->name }}">
    <meta name="user-role" content="{{ auth()->user()?->roles->first()?->name }}">
    <div class="py-3">
        <citizen-dashboard />
    </div>
</x-app-layout>

@push('modals')
    <div id="chat-widget-root">
        <chat-widget></chat-widget>
    </div>
@endpush
