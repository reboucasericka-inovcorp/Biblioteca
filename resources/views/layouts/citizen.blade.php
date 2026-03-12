<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="biblioteca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

    <div class="min-h-screen">
        <x-site-header />

        @if (isset($header))
            <header class="bg-white border-b border-steel-gray">
                <div class="max-w-[1400px] mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main class="py-6">
            <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8">
                <div id="app">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>

    {{-- Mobile nav --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-steel-gray safe-area-pb z-50">
        <div class="flex justify-around py-2">
            <a href="{{ route('dashboard.citizen') }}" class="text-night-blue/70 text-xs">Dashboard</a>
            <a href="{{ route('books.index') }}" class="text-night-blue/70 text-xs">Livros</a>
            <a href="{{ route('authors.index') }}" class="text-night-blue/70 text-xs">Autores</a>
            <a href="{{ route('publishers.index') }}" class="text-night-blue/70 text-xs">Editoras</a>
            <a href="{{ route('requisitions.index') }}" class="text-night-blue/70 text-xs">Requisições</a>
        </div>
    </nav>

    <div class="md:hidden h-16"></div>

    @stack('modals')
    @livewireScripts
</body>
</html>
