<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="biblioteca">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-layout-user-meta />
    <title>{{ config('app.name', 'Laravel') }} - Biblioteca Digital</title>
    <link rel="icon" type="image/png" href="{{ asset('images/inov.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-base-100">

<div id="app" data-auth="{{ auth()->check() ? '1' : '0' }}">
    <x-site-header />
    @include('components.public-store-nav')
    @if (auth()->check() && ! auth()->user()->hasRole('Admin'))
        <div class="bg-white border-b border-steel-gray">
            <div class="max-w-[1400px] mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <h2 class="font-semibold text-xl text-night-blue leading-tight shrink-0">
                        {{ $citizenAreaHeading ?? ('Bem vindo ' . auth()->user()->name) }}
                    </h2>
                    @include('components.nav.citizen-menu', ['variant' => 'header'])
                </div>
            </div>
        </div>
    @endif
    {{ $slot }}
    @if (auth()->check() && ! auth()->user()->hasRole('Admin'))
        <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-steel-gray safe-area-pb z-50">
            <div class="flex flex-wrap justify-around gap-x-1 gap-y-2 py-2 px-1">
                @include('components.nav.citizen-menu', ['variant' => 'mobile'])
            </div>
        </nav>
        <div class="md:hidden h-16"></div>
    @endif
</div>

</body>

</html>