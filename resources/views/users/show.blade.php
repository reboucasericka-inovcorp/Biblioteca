<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-night-blue leading-tight">
                Detalhe do Cidadão
            </h2>
            <a href="{{ route('requisitions.index') }}" class="text-sm text-night-blue/70 hover:text-night-blue">
                ← Voltar às Requisições
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Dados do utilizador --}}
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-night-blue mb-4">Informação</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-night-blue/70">Nome</dt>
                        <dd class="font-medium text-night-blue">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-night-blue/70">Email</dt>
                        <dd class="font-medium text-night-blue">{{ $user->email }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Histórico de requisições --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <h3 class="text-lg font-semibold text-night-blue p-6 pb-0">Histórico de Requisições</h3>
                <div class="p-6">
                    @if ($user->requisitions->isEmpty())
                        <p class="text-night-blue/70">Nenhuma requisição registada.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="table-auto w-full border">
                                <thead>
                                    <tr class="bg-steel-gray/30">
                                        <th class="p-3 text-left text-sm font-medium text-night-blue">Nº</th>
                                        <th class="p-3 text-left text-sm font-medium text-night-blue">Livro</th>
                                        <th class="p-3 text-left text-sm font-medium text-night-blue">Data requisição</th>
                                        <th class="p-3 text-left text-sm font-medium text-night-blue">Data devolução</th>
                                        <th class="p-3 text-left text-sm font-medium text-night-blue">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($user->requisitions as $r)
                                        <tr class="border-t border-steel-gray/50 hover:bg-steel-gray/20">
                                            <td class="p-3 font-mono text-sm">{{ $r->sequential_number }}</td>
                                            <td class="p-3">
                                                @if ($r->book)
                                                    <a href="{{ route('books.show', $r->book) }}" class="text-electric-blue hover:underline">
                                                        {{ $r->book->name }}
                                                    </a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="p-3 text-sm">{{ $r->request_date?->format('d/m/Y') ?? '-' }}</td>
                                            <td class="p-3 text-sm">{{ $r->return_date?->format('d/m/Y') ?? ($r->due_date?->format('d/m/Y') ?? '-') }}</td>
                                            <td class="p-3">
                                                @php
                                                    $statusClasses = [
                                                        'active' => 'bg-electric-blue/20 text-electric-blue',
                                                        'returned' => 'bg-neon-green/30 text-night-blue',
                                                        'late' => 'bg-amber-100 text-amber-800',
                                                    ];
                                                    $statusLabels = [
                                                        'active' => 'Ativa',
                                                        'returned' => 'Devolvida',
                                                        'late' => 'Atrasada',
                                                    ];
                                                @endphp
                                                <span class="px-2 py-1 text-xs font-medium rounded {{ $statusClasses[$r->status] ?? 'bg-steel-gray/30 text-night-blue' }}">
                                                    {{ $statusLabels[$r->status] ?? $r->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
