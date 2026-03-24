@php
    $variant = $variant ?? 'header';
@endphp

@if ($variant === 'mobile')
    <a href="{{ route('dashboard.citizen') }}" class="text-night-blue/70 text-xs">Dashboard</a>
    <a href="{{ route('books.index') }}" class="text-night-blue/70 text-xs">Livros</a>
    <a href="{{ route('authors.index') }}" class="text-night-blue/70 text-xs">Autores</a>
    <a href="{{ route('publishers.index') }}" class="text-night-blue/70 text-xs">Editoras</a>
    <a href="{{ route('requisitions.index') }}" class="text-night-blue/70 text-xs">Requisições</a>
    <a href="{{ route('purchases.index') }}" class="text-night-blue/70 text-xs">Compras</a>
@else
    <nav class="flex flex-wrap gap-x-5 gap-y-2 text-sm font-medium w-full lg:flex-1 lg:min-w-0 lg:justify-end" aria-label="Área do cliente">
        <a href="{{ route('dashboard.citizen') }}" @class(['text-night-blue underline decoration-2 underline-offset-4' => request()->routeIs('dashboard.citizen'), 'text-night-blue/75 hover:text-night-blue' => ! request()->routeIs('dashboard.citizen')])>Dashboard</a>
        <a href="{{ route('books.index') }}" @class(['text-night-blue underline decoration-2 underline-offset-4' => request()->routeIs('books.*'), 'text-night-blue/75 hover:text-night-blue' => ! request()->routeIs('books.*')])>Livros</a>
        <a href="{{ route('authors.index') }}" @class(['text-night-blue underline decoration-2 underline-offset-4' => request()->routeIs('authors.*'), 'text-night-blue/75 hover:text-night-blue' => ! request()->routeIs('authors.*')])>Autores</a>
        <a href="{{ route('publishers.index') }}" @class(['text-night-blue underline decoration-2 underline-offset-4' => request()->routeIs('publishers.*'), 'text-night-blue/75 hover:text-night-blue' => ! request()->routeIs('publishers.*')])>Editoras</a>
        <a href="{{ route('requisitions.index') }}" @class(['text-night-blue underline decoration-2 underline-offset-4' => request()->routeIs('requisitions.*'), 'text-night-blue/75 hover:text-night-blue' => ! request()->routeIs('requisitions.*')])>Requisições</a>
        <a href="{{ route('purchases.index') }}" @class(['text-night-blue underline decoration-2 underline-offset-4' => request()->routeIs('purchases.*'), 'text-night-blue/75 hover:text-night-blue' => ! request()->routeIs('purchases.*')])>Minhas compras</a>
    </nav>
@endif
