@php
    $itemClass = $itemClass ?? 'block px-3 py-2 rounded hover:bg-white/10';
@endphp
<a href="{{ route('dashboard.admin') }}" class="{{ $itemClass }}">Dashboard</a>
<a href="{{ route('books.index') }}" class="{{ $itemClass }}">Livros</a>
<a href="{{ route('authors.index') }}" class="{{ $itemClass }}">Autores</a>
<a href="{{ route('publishers.index') }}" class="{{ $itemClass }}">Editoras</a>
<a href="{{ route('requisitions.index') }}" class="{{ $itemClass }}">Requisições</a>
<a href="{{ route('reviews.index') }}" class="{{ $itemClass }}">Reviews</a>
<a href="{{ route('users.index') }}" class="{{ $itemClass }}">Utilizadores</a>
<a href="{{ route('orders.index') }}" class="{{ $itemClass }}">Pedidos</a>
<a href="{{ route('logs.index') }}" class="{{ $itemClass }}">Logs</a>
<a href="{{ route('chat.index') }}" class="{{ $itemClass }}">Chat</a>
