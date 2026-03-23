<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">
            Chat Interno
        </h2>
    </x-slot>

    <meta name="user-id" content="{{ (int) auth()->id() }}">
    <meta name="user-name" content="{{ auth()->user()?->name }}">
    <meta name="user-role" content="{{ auth()->user()?->roles->first()?->name }}">
    <div id="app" class="h-[90vh] overflow-hidden">
        <chat-layout></chat-layout>
    </div>
</x-app-layout>