<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="biblioteca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Admin</title>
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
        {{-- Navbar horizontal (Admin )--}}
        <header class="bg-white shadow-sm border-b border-steel-gray">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center gap-6">
                        <a href="{{ route('dashboard.admin') }}" class="font-bold text-night-blue text-lg">
                            Inovcorp Library
                        </a>
                        <nav class="hidden md:flex gap-1">
                        <a href="{{ route('dashboard.admin') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('dashboard.admin') ? 'bg-base-content text-base-100' : 'text-base-content/70 hover:bg-base-content/10 hover:text-base-content' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('books.index') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('books.*') ? 'bg-base-content text-base-100' : 'text-base-content/70 hover:bg-base-content/10 hover:text-base-content' }}">
                            Livros
                        </a>
                        <a href="{{ route('authors.index') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('authors.*') ? 'bg-base-content text-base-100' : 'text-base-content/70 hover:bg-base-content/10 hover:text-base-content' }}">
                            Autores
                        </a>
                        <a href="{{ route('publishers.index') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('publishers.*') ? 'bg-base-content text-base-100' : 'text-base-content/70 hover:bg-base-content/10 hover:text-base-content' }}">
                            Editoras
                        </a>
                        <a href="{{ route('requisitions.index') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('requisitions.*') ? 'bg-base-content text-base-100' : 'text-base-content/70 hover:bg-base-content/10 hover:text-base-content' }}">
                            Requisições
                        </a>
                        <a href="{{ route('users.index') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('users.*') ? 'bg-base-content text-base-100' : 'text-base-content/70 hover:bg-base-content/10 hover:text-base-content' }}">
                            Utilizadores
                        </a>
                        <a href="{{ route('orders.index') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('orders.*') ? 'bg-base-content text-base-100' : 'text-base-content/70 hover:bg-base-content/10 hover:text-base-content' }}">
                            Encomendas
                        </a>
                        <a href="{{ route('reviews.index') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('reviews.*') ? 'bg-base-content text-base-100' : 'text-base-content/70 hover:bg-base-content/10 hover:text-base-content' }}">
                            Reviews
                        </a>
                    </nav>
                </div>
                <div class="flex items-center gap-2">
                    <x-dropdown align="right" width="72">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-base-content/30 transition">
                                    <img class="size-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                </button>
                            @else
                                <button type="button" class="inline-flex items-center px-3 py-2 text-sm font-medium text-base-content/70 hover:text-base-content rounded-lg transition">
                                    {{ Auth::user()->name }}
                                    <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            @endif
                        </x-slot>
                        <x-slot name="content">
                            <div class="flex items-center gap-3 px-4 py-3 border-b border-base-200">
                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                    <img class="size-10 shrink-0 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="font-medium text-base-content break-words">{{ Auth::user()->name }}</div>
                                    <div class="text-sm text-base-content/60 break-words">{{ Auth::user()->email }}</div>
                                </div>
                            </div>
                            <x-dropdown-link href="{{ route('profile.show') }}">
                                Perfil
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-start text-sm text-gray-700 hover:bg-gray-100 focus:outline-none transition">
                                    Sair
                                </button>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </header>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-base-100 shadow-sm border-b border-base-300 lg:hidden">
                <div class="flex items-center justify-between px-4 py-3">
                    <a href="{{ route('dashboard.admin') }}" class="text-xl font-semibold text-base-content">Inovcorp Library</a>
                    <span class="badge badge-secondary badge-sm">Admin</span>
                </div>
            </header>

            @if (isset($header))
                <header class="bg-base-100 shadow-sm border-b border-base-300">
                    <div class="max-w-[1400px] mx-auto py-6 px-4 sm:px-6 lg:px-8">
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
            <a href="{{ route('dashboard.admin') }}" class="text-base-content/70 text-sm">Dashboard</a>
            <a href="{{ route('books.index') }}" class="text-base-content/70 text-sm">Livros</a>
            <a href="{{ route('authors.index') }}" class="text-base-content/70 text-sm">Autores</a>
            <a href="{{ route('publishers.index') }}" class="text-base-content/70 text-sm">Editoras</a>
            <a href="{{ route('requisitions.index') }}" class="text-base-content/70 text-sm">Requisições</a>
            <a href="{{ route('users.index') }}" class="text-base-content/70 text-sm">Utilizadores</a>
            <a href="{{ route('reviews.index') }}" class="text-base-content/70 text-sm">Reviews</a>
        </div>
    </nav>

    <div class="lg:hidden h-16"></div>

    @stack('modals')
    @livewireScripts
</body>
</html>
