<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="font-sans antialiased bg-slate-100">
    <x-banner />

    <div class="flex min-h-screen">
        {{-- Sidebar Admin --}}
        <aside class="w-64 bg-slate-900 text-white shrink-0 hidden lg:block">
            <div class="p-6 sticky top-0">
                <a href="{{ route('dashboard') }}" class="block mb-8">
                    <h2 class="text-xl font-bold text-white">
                        Inovcorp Library
                    </h2>
                </a>

                <span class="badge badge-secondary badge-sm mb-4">Admin Mode</span>

                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('books.index') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('books.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Livros
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('authors.index') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('authors.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Autores
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('publishers.index') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('publishers.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Editoras
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('requisitions.index') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('requisitions.*') ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Requisições
                        </a>
                    </li>
                </ul>

                <div class="mt-8 pt-6 border-t border-slate-700">
                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-slate-400 hover:text-white text-sm">
                        Perfil
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="block px-4 py-2 text-slate-400 hover:text-white text-sm w-full text-left">
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-white shadow-sm border-b border-slate-200 lg:hidden">
                <div class="flex items-center justify-between px-4 py-3">
                    <a href="{{ route('dashboard') }}" class="font-bold text-slate-800">Inovcorp Library</a>
                    <span class="badge badge-secondary badge-sm">Admin</span>
                </div>
            </header>

            @if (isset($header))
                <header class="bg-white shadow-sm border-b border-slate-200">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="flex-1 p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Mobile nav --}}
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 safe-area-pb z-50">
        <div class="flex justify-around py-2">
            <a href="{{ route('dashboard') }}" class="text-slate-600 text-xs">Dashboard</a>
            <a href="{{ route('books.index') }}" class="text-slate-600 text-xs">Livros</a>
            <a href="{{ route('authors.index') }}" class="text-slate-600 text-xs">Autores</a>
            <a href="{{ route('publishers.index') }}" class="text-slate-600 text-xs">Editoras</a>
            <a href="{{ route('requisitions.index') }}" class="text-slate-600 text-xs">Requisições</a>
        </div>
    </nav>

    <div class="lg:hidden h-16"></div>

    @stack('modals')
    @livewireScripts
</body>
</html>
