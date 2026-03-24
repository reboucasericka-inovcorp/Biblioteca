<x-public-layout heading="Minhas compras">
    <section class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <p class="text-sm text-base-content/70 mb-6">Lista apenas das encomendas associadas à sua conta.</p>

        <div class="bg-white rounded-lg border border-base-200 shadow-sm overflow-hidden">
            @if ($orders->isEmpty())
                <p class="p-6 text-base-content/70">Ainda não tem encomendas registadas.</p>
            @else
                <ul class="divide-y divide-base-200">
                    @foreach ($orders as $order)
                        <li class="p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <p class="font-medium text-base-content">Encomenda #{{ $order->id }}</p>
                                <p class="text-sm text-base-content/60">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="text-sm font-medium text-base-content">{{ number_format($order->total, 2, ',', ' ') }} €</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $order->status === \App\Models\Order::STATUS_PAID ? 'bg-success/20 text-success' : ($order->status === \App\Models\Order::STATUS_CANCELLED ? 'bg-base-200 text-base-content/80' : 'bg-warning/20 text-warning-content') }}">
                                    {{ $order->status === \App\Models\Order::STATUS_PAID ? 'Pago' : ($order->status === \App\Models\Order::STATUS_CANCELLED ? 'Cancelado' : 'Pendente') }}
                                </span>
                                <a href="{{ route('purchases.show', $order) }}" class="text-sm font-medium link link-primary">
                                    Detalhe
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="px-4 py-3 border-t border-base-200">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </section>
</x-public-layout>
