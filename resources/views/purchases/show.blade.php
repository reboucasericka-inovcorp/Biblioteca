<x-public-layout :heading="'Encomenda #' . $order->id">
    <section class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('purchases.index') }}" class="inline-flex items-center px-4 py-2 border border-base-300 text-base-content text-sm font-medium rounded-md bg-white hover:bg-base-200 transition">
                ← Voltar às minhas compras
            </a>
        </div>

        <div class="bg-white rounded-lg border border-base-200 shadow-sm overflow-hidden">
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <dt class="text-sm font-medium text-base-content/60">Total</dt>
                        <dd class="mt-1 font-medium text-base-content">{{ number_format($order->total, 2, ',', ' ') }} €</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-base-content/60">Estado</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $order->status === \App\Models\Order::STATUS_PAID ? 'bg-success/20 text-success' : ($order->status === \App\Models\Order::STATUS_CANCELLED ? 'bg-base-200 text-base-content/80' : 'bg-warning/20 text-warning-content') }}">
                                @if ($order->status === \App\Models\Order::STATUS_PAID)
                                    Pago
                                @elseif ($order->status === \App\Models\Order::STATUS_CANCELLED)
                                    Cancelado
                                @else
                                    Pendente
                                @endif
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-base-content/60">Data</dt>
                        <dd class="mt-1 font-medium text-base-content">{{ $order->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>

                @if ($order->shipping_address || $order->shipping_city)
                    <h3 class="font-semibold text-[#000020] mb-3">Morada de entrega</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        @if ($order->shipping_address)
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-base-content/60">Morada</dt>
                                <dd class="mt-1 font-medium text-base-content">{{ $order->shipping_address }}</dd>
                            </div>
                        @endif
                        @if ($order->shipping_city)
                            <div>
                                <dt class="text-sm font-medium text-base-content/60">Cidade</dt>
                                <dd class="mt-1 font-medium text-base-content">{{ $order->shipping_city }}</dd>
                            </div>
                        @endif
                        @if ($order->shipping_postal_code)
                            <div>
                                <dt class="text-sm font-medium text-base-content/60">Código postal</dt>
                                <dd class="mt-1 font-medium text-base-content">{{ $order->shipping_postal_code }}</dd>
                            </div>
                        @endif
                        @if ($order->shipping_country)
                            <div>
                                <dt class="text-sm font-medium text-base-content/60">País</dt>
                                <dd class="mt-1 font-medium text-base-content">{{ $order->shipping_country }}</dd>
                            </div>
                        @endif
                    </dl>
                @endif

                <h3 class="font-semibold text-[#000020] mb-3">Itens</h3>
                <div class="overflow-x-auto border border-base-200 rounded-lg">
                    <table class="min-w-full divide-y divide-base-200">
                        <thead class="bg-base-200/50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-base-content/70 uppercase tracking-wider">Livro</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-base-content/70 uppercase tracking-wider">Qtd</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-base-content/70 uppercase tracking-wider">Preço unit.</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-base-content/70 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-base-200">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-base-content">{{ $item->book_title ?? $item->book?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-base-content text-right">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-base-content text-right">{{ number_format($item->unit_price, 2, ',', ' ') }} €</td>
                                    <td class="px-4 py-3 text-sm text-base-content text-right">{{ number_format($item->total, 2, ',', ' ') }} €</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($order->events->isNotEmpty())
                    <h3 class="font-semibold text-[#000020] mt-6 mb-3">Historial do pedido</h3>
                    <ul class="space-y-2 text-sm">
                        @foreach ($order->events as $event)
                            <li class="flex flex-wrap items-start gap-3 py-2 border-l-2 border-base-300 pl-4 ml-1">
                                <span class="font-medium text-base-content">{{ match ($event->event) {
                                    'order_created' => 'Pedido criado',
                                    'payment_started' => 'Checkout iniciado',
                                    'payment_completed' => 'Pagamento concluído',
                                    'payment_expired' => 'Pagamento expirado / cancelado',
                                    'order_cancelled' => 'Pedido cancelado',
                                    default => $event->event,
                                } }}</span>
                                <span class="text-base-content/60 shrink-0">{{ $event->created_at->format('d/m/Y H:i') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </section>
</x-public-layout>
