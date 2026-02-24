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
<body class="font-sans antialiased bg-steel-gray/20">
    <x-banner />

    <div class="min-h-screen">
        {{-- Navbar horizontal (cidadão) --}}
        <header class="bg-white shadow-sm border-b border-steel-gray">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center gap-6">
                        <a href="{{ route('dashboard.citizen') }}" class="font-bold text-night-blue text-lg">
                            Inovcorp Library
                        </a>
                        <nav class="hidden md:flex gap-1">
                            <a href="{{ route('dashboard.citizen') }}" class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('dashboard.citizen') ? 'bg-steel-gray/30 text-night-blue font-medium' : 'text-night-blue/70 hover:bg-steel-gray/20' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('books.index') }}" class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('books.*') ? 'bg-steel-gray/30 text-night-blue font-medium' : 'text-night-blue/70 hover:bg-steel-gray/20' }}">
                                Livros
                            </a>
                            <a href="{{ route('authors.index') }}" class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('authors.*') ? 'bg-steel-gray/30 text-night-blue font-medium' : 'text-night-blue/70 hover:bg-steel-gray/20' }}">
                                Autores
                            </a>
                            <a href="{{ route('publishers.index') }}" class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('publishers.*') ? 'bg-steel-gray/30 text-night-blue font-medium' : 'text-night-blue/70 hover:bg-steel-gray/20' }}">
                                Editoras
                            </a>
                            <a href="{{ route('requisitions.index') }}" class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('requisitions.*') ? 'bg-steel-gray/30 text-night-blue font-medium' : 'text-night-blue/70 hover:bg-steel-gray/20' }}">
                                Minhas Requisições
                            </a>
                        </nav>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('profile.show') }}" class="px-3 py-2 text-sm text-night-blue/70 hover:text-night-blue">
                            Perfil
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="px-3 py-2 text-sm text-night-blue/70 hover:text-night-blue">
                                Sair
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        @if (isset($header))
            <header class="bg-white border-b border-steel-gray">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{ $slot }}
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
