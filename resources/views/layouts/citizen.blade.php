<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="biblioteca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-layout-user-meta />
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/inov.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="font-sans antialiased bg-steel-gray/20" data-auth="{{ auth()->check() ? '1' : '0' }}">
    <x-banner />

    {{-- #app envolve cabeçalho + conteúdo para o Vue compilar site-header (ex.: header-google-search) e google-books-search no slot de header --}}
    <div id="app" class="min-h-screen">
        <x-site-header />

        @include('components.public-store-nav')

        <header class="bg-white border-b border-steel-gray">
            <div class="max-w-[1400px] mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0 shrink-0">
                        @isset($header)
                            {{ $header }}
                        @endisset
                    </div>
                    @include('components.nav.citizen-menu', ['variant' => 'header'])
                </div>
            </div>
        </header>

        <main class="py-6">
            <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    {{-- Mobile nav --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-steel-gray safe-area-pb z-50">
        <div class="flex flex-wrap justify-around gap-x-1 gap-y-2 py-2 px-1">
            @include('components.nav.citizen-menu', ['variant' => 'mobile'])
        </div>
    </nav>

    <div class="md:hidden h-16"></div>

    @stack('modals')
    <div id="chat-widget-root">
        <chat-widget></chat-widget>
    </div>
    @livewireScripts
</body>
</html>
