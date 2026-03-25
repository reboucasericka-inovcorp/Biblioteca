@php
    $itemClass = $itemClass ?? 'block px-3 py-2 rounded hover:bg-white/10';
@endphp
<a href="{{ route('dashboard.admin') }}" class="{{ $itemClass }}">Dashboard</a>
<a href="{{ route('books.index') }}" class="{{ $itemClass }}">Livros</a>
<a href="{{ route('authors.index') }}" class="{{ $itemClass }}">Autores</a>
<a href="{{ route('publishers.index') }}" class="{{ $itemClass }}">Editoras</a>
<a href="{{ route('requisitions.index') }}" class="{{ $itemClass }}">Requisições</a>
<a href="{{ route('admin.suggestions') }}" class="{{ $itemClass }}">
    Sugestões (<span id="admin-suggestions-pending-count">&nbsp;</span>)
</a>
<a href="{{ route('reviews.index') }}" class="{{ $itemClass }}">Reviews</a>
<a href="{{ route('users.index') }}" class="{{ $itemClass }}">Utilizadores</a>
<a href="{{ route('orders.index') }}" class="{{ $itemClass }}">Pedidos</a>
<a href="{{ route('logs.index') }}" class="{{ $itemClass }}">Logs</a>
<a href="{{ route('chat.index') }}" class="{{ $itemClass }}">Chat</a>

<script>
    (function () {
        // Evita múltiplos fetches se o component for renderizado mais do que uma vez.
        if (window.__adminSuggestionsPendingBadgePromise) return;

        window.__adminSuggestionsPendingBadgePromise = (async () => {
            if (document.readyState === 'loading') {
                await new Promise((resolve) => {
                    document.addEventListener('DOMContentLoaded', resolve, { once: true });
                });
            }

            const el = document.getElementById('admin-suggestions-pending-count');
            if (!el) return;

            // Evita "piscada" feia: só mostra "..." se a resposta demorar.
            let ellipsisTimer = setTimeout(() => {
                el.textContent = '...';
            }, 150);

            try {
                const res = await fetch('/api/book-suggestions?status=pending', { credentials: 'include' });
                if (!res.ok) return;
                const json = await res.json();
                const total = json?.data?.total ?? json?.data?.data?.length ?? 0;
                clearTimeout(ellipsisTimer);
                el.textContent = String(total);
            } catch (e) {
                // falha silenciosa: não deve quebrar o layout
                clearTimeout(ellipsisTimer);
            }
        })();
    })();
</script>
