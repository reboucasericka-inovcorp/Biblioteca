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
            <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
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
            </div>
        </header>

        @if (isset($header))
            <header class="bg-white border-b border-steel-gray">
                <div class="max-w-[1400px] mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main class="py-6">
            <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8">
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
