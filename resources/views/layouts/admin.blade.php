<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="biblioteca">
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
<body class="font-sans antialiased bg-base-200">
    <x-banner />

    <div class="flex min-h-screen">
        {{-- Sidebar Admin --}}
        <aside class="w-64 bg-base-300 text-base-content shrink-0 hidden lg:block">
            <div class="p-6 sticky top-0">
                <a href="{{ route('dashboard') }}" class="block mb-8">
                    <h2 class="text-xl font-semibold text-base-content">
                        Inovcorp Library
                    </h2>
                </a>

                <span class="badge badge-secondary badge-sm mb-4">Admin Mode</span>

                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-base-content text-base-100' : 'text-base-content/70 hover:bg-base-content/10 hover:text-base-content' }}">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('books.index') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('books.*') ? 'bg-base-content text-base-100' : 'text-base-content/70 hover:bg-base-content/10 hover:text-base-content' }}">
                            Livros
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('authors.index') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('authors.*') ? 'bg-base-content text-base-100' : 'text-base-content/70 hover:bg-base-content/10 hover:text-base-content' }}">
                            Autores
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('publishers.index') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('publishers.*') ? 'bg-base-content text-base-100' : 'text-base-content/70 hover:bg-base-content/10 hover:text-base-content' }}">
                            Editoras
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('requisitions.index') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('requisitions.*') ? 'bg-base-content text-base-100' : 'text-base-content/70 hover:bg-base-content/10 hover:text-base-content' }}">
                            Requisições
                        </a>
                    </li>
                </ul>

                <div class="mt-8 pt-6 border-t border-base-content/20">
                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-base-content/70 hover:text-base-content text-sm">
                        Perfil
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="block px-4 py-2 text-base-content/70 hover:text-base-content text-sm w-full text-left">
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-base-100 shadow-sm border-b border-base-300 lg:hidden">
                <div class="flex items-center justify-between px-4 py-3">
                    <a href="{{ route('dashboard') }}" class="text-xl font-semibold text-base-content">Inovcorp Library</a>
                    <span class="badge badge-secondary badge-sm">Admin</span>
                </div>
            </header>

            @if (isset($header))
                <header class="bg-base-100 shadow-sm border-b border-base-300">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="flex-1 p-6 lg:p-8 space-y-4">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Mobile nav --}}
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-base-100 border-t border-base-300 safe-area-pb z-50">
        <div class="flex justify-around py-2">
            <a href="{{ route('dashboard') }}" class="text-base-content/70 text-sm">Dashboard</a>
            <a href="{{ route('books.index') }}" class="text-base-content/70 text-sm">Livros</a>
            <a href="{{ route('authors.index') }}" class="text-base-content/70 text-sm">Autores</a>
            <a href="{{ route('publishers.index') }}" class="text-base-content/70 text-sm">Editoras</a>
            <a href="{{ route('requisitions.index') }}" class="text-base-content/70 text-sm">Requisições</a>
        </div>
    </nav>

    <div class="lg:hidden h-16"></div>

    @stack('modals')
    @livewireScripts
</body>
</html>
