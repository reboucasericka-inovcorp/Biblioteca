<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="font-sans antialiased bg-slate-100">
    <x-banner />

    <div class="min-h-screen">
        {{-- Navbar horizontal (cidadão) --}}
        <header class="bg-white shadow-sm border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center gap-6">
                        <a href="{{ route('dashboard') }}" class="font-bold text-slate-800 text-lg">
                            Inovcorp Library
                        </a>
                        <nav class="hidden md:flex gap-1">
                            <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('dashboard') ? 'bg-slate-100 text-slate-900 font-medium' : 'text-slate-600 hover:bg-slate-50' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('books.index') }}" class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('books.*') ? 'bg-slate-100 text-slate-900 font-medium' : 'text-slate-600 hover:bg-slate-50' }}">
                                Livros
                            </a>
                            <a href="{{ route('authors.index') }}" class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('authors.*') ? 'bg-slate-100 text-slate-900 font-medium' : 'text-slate-600 hover:bg-slate-50' }}">
                                Autores
                            </a>
                            <a href="{{ route('publishers.index') }}" class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('publishers.*') ? 'bg-slate-100 text-slate-900 font-medium' : 'text-slate-600 hover:bg-slate-50' }}">
                                Editoras
                            </a>
                            <a href="{{ route('requisitions.index') }}" class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('requisitions.*') ? 'bg-slate-100 text-slate-900 font-medium' : 'text-slate-600 hover:bg-slate-50' }}">
                                Minhas Requisições
                            </a>
                        </nav>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('profile.show') }}" class="px-3 py-2 text-sm text-slate-600 hover:text-slate-900">
                            Perfil
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="px-3 py-2 text-sm text-slate-600 hover:text-slate-900">
                                Sair
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        @if (isset($header))
            <header class="bg-white border-b border-slate-200">
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
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 safe-area-pb z-50">
        <div class="flex justify-around py-2">
            <a href="{{ route('dashboard') }}" class="text-slate-600 text-xs">Dashboard</a>
            <a href="{{ route('books.index') }}" class="text-slate-600 text-xs">Livros</a>
            <a href="{{ route('authors.index') }}" class="text-slate-600 text-xs">Autores</a>
            <a href="{{ route('publishers.index') }}" class="text-slate-600 text-xs">Editoras</a>
            <a href="{{ route('requisitions.index') }}" class="text-slate-600 text-xs">Requisições</a>
        </div>
    </nav>

    <div class="md:hidden h-16"></div>

    @stack('modals')
    @livewireScripts
</body>
</html>
