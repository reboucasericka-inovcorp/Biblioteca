<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="biblioteca">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-layout-user-meta />

    <title>{{ config('app.name', 'Laravel') }} - Admin</title>

    <link rel="icon" type="image/png" href="{{ asset('images/inov.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased bg-gray-100">

    <x-banner />

    <div class="min-h-screen flex">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-[#000020] text-white flex flex-col">

            <!-- LOGO -->
            <div class="p-6 text-lg font-bold border-b border-white/20">Inovcorp Admin</div>

            <!-- PERFIL -->
            <div class="flex items-center gap-3 p-4 border-b border-white/20">

                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                <img
                    class="h-10 w-10 rounded-full object-cover"
                    src="{{ auth()->user()->profile_photo_url }}"
                    alt="{{ auth()->user()->name }}">
                @else
                <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center text-sm font-semibold">
                    {{ mb_substr(auth()->user()->name,0,1) }}
                </div>
                @endif

                <div>
                    <div class="text-sm font-semibold uppercase">{{ auth()->user()->name }}</div>

                    <div class="text-xs text-white/70">Admin</div>
                </div>

            </div>

            <!-- MENU -->
            <nav class="flex-1 p-4 space-y-1 text-sm">
                @include('components.nav.admin-menu', ['itemClass' => 'block px-3 py-2 rounded hover:bg-white/10'])
            </nav>

            <!-- AÇÕES -->
            <div class="p-4 border-t border-white/20 space-y-2 text-sm">

                <a href="{{ route('profile.show') }}" class="block px-3 py-2 rounded hover:bg-white/10">
                    Perfil
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="text-left px-3 py-2 rounded text-red-400 hover:bg-white/10">
                        Sair
                    </button>
                </form>

            </div>

        </aside>

        <!-- CONTEÚDO -->
        <div class="flex-1 flex flex-col">

            <header class="bg-white shadow px-6 py-4">
                <h1 class="font-semibold text-lg text-gray-900">
                    Admin Panel
                </h1>
            </header>

            @if(isset($header))
            <div class="px-6 py-4 bg-white border-b border-gray-200">
                <div class="max-w-none px-4 py-4 md:px-6">
                    {{ $header }}
                </div>
            </div>
            @endif

            <main class="p-3 md:p-4">
                <div class="max-w-none px-4 py-4 md:px-6">
                    <div id="app">
                        {{ $slot }}
                    </div>
                </div>
            </main>

        </div>

    </div>

    @stack('modals')
    @livewireScripts
    <div id="chat-widget-root">
        <chat-widget></chat-widget>
    </div>
</body>
    

</html>