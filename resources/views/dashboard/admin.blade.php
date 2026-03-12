<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            Bem-vindo, {{ auth()->user()->name }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <div class="card shadow bg-base-100">
            <div class="card-body p-6">
    <div class="space-y-8">
        <!-- MÉTRICAS -->
        <section>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Métricas gerais</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <div class="bg-white shadow rounded-lg p-4 border border-gray-100">
                    <div class="text-sm text-gray-500">Livros</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ $totalBooks }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-4 border border-gray-100">
                    <div class="text-sm text-gray-500">Autores</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ $totalAuthors }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-4 border border-gray-100">
                    <div class="text-sm text-gray-500">Editoras</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ $totalPublishers }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-4 border border-gray-100">
                    <div class="text-sm text-gray-500">Utilizadores</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ $totalUsers }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-4 border border-gray-100">
                    <div class="text-sm text-gray-500">Requisições</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ $totalRequisitions }}</div>
                </div>
                <div class="bg-white shadow rounded-lg p-4 border border-gray-100">
                    <div class="text-sm text-gray-500">Requisições em atraso</div>
                    <div class="mt-2 text-2xl font-bold text-red-600">{{ $lateRequisitions }}</div>
                </div>
            </div>
        </section>

        <!-- ÚLTIMOS LIVROS E REQUISIÇÕES RECENTES -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow rounded-lg p-4 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Últimos livros</h3>
                <ul class="divide-y divide-gray-100 text-sm">
                    @forelse($latestBooks as $book)
                        <li class="py-2 flex items-center justify-between gap-3">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $book->name }}</div>
                                <div class="text-xs text-gray-500">
                                    @if($book->publisher)
                                        {{ $book->publisher->name }}
                                    @else
                                        Sem editora
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('books.edit', $book) }}"
                               class="text-xs text-indigo-600 hover:underline">
                                Editar
                            </a>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-500">Nenhum livro registado.</li>
                    @endforelse
                </ul>
            </div>

            <div class="bg-white shadow rounded-lg p-4 border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Requisições recentes</h3>
                <ul class="divide-y divide-gray-100 text-sm">
                    @forelse($recentRequisitions as $requisition)
                        <li class="py-2 flex flex-col gap-1">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold text-gray-900">
                                    {{ $requisition->book?->name ?? 'Livro removido' }}
                                </div>
                                @php
                                    $statusClass = match ($requisition->status) {
                                        \App\Models\Requisition::STATUS_RETURNED => 'bg-green-100 text-green-800',
                                        \App\Models\Requisition::STATUS_ACTIVE => 'bg-yellow-100 text-yellow-800',
                                        \App\Models\Requisition::STATUS_LATE => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs {{ $statusClass }}">
                                    {{ ucfirst($requisition->status) }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $requisition->user?->name ?? 'Utilizador removido' }}
                                • Pedido em {{ optional($requisition->request_date)->format('d/m/Y H:i') }}
                            </div>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-500">Nenhuma requisição registada.</li>
                    @endforelse
                </ul>
            </div>
        </section>

        <!-- COMPONENTES EXISTENTES DO ADMIN -->
        <section class="space-y-6">
            <sales-dashboard />

            <admin-suggestions />

            <google-books-search
                :user-is-admin="@json(Auth::user()->hasRole('Admin'))"
            />
        </section>
    </div>
            </div>
        </div>
    </div>
</x-admin-layout>