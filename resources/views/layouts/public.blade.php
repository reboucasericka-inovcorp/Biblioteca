<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Biblioteca Digital</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white">

    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex justify-between h-16">
                <a href="{{ url('/') }}" class="flex items-center font-bold text-slate-800">
                    Inovcorp Library
                </a>
                <div class="flex items-center gap-4">                    
                    <a href="{{ route('login') }}" class="text-slate-600 hover:text-slate-900 text-sm font-medium">
                        Entrar
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-white text-sm font-medium">
                            Criar Conta
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    {{ $slot ?? '' }}

</body>
</html>
