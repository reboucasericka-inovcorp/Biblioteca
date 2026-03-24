{{-- Metas para JS (chat, axios, etc.): só no <head>, nunca no corpo da página. --}}
@auth
    <meta name="user-id" content="{{ (int) auth()->id() }}">
    <meta name="user-name" content="{{ auth()->user()->name }}">
    <meta name="user-email" content="{{ auth()->user()->email }}">
    <meta name="user-role" content="{{ auth()->user()?->roles->first()?->name }}">
@endauth
