<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-night-blue leading-tight">
            Encomenda #{{ $order->id }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 rounded shadow border border-steel-gray/50">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div>
                    <dt class="text-sm text-base-content/60">Cliente</dt>
                    <dd class="font-medium">{{ $order->customer_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-base-content/60">Total</dt>
                    <dd class="font-medium">{{ number_format($order->total, 2, ',', ' ') }} €</dd>
                </div>
                <div>
                    <dt class="text-sm text-base-content/60">Estado</dt>
                    <dd>
                        <span class="badge {{ $order->status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                            {{ $order->status === 'paid' ? 'Pago' : 'Pendente' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-base-content/60">Data</dt>
                    <dd class="font-medium">{{ $order->created_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>

            <h3 class="font-semibold text-base mb-3">Itens</h3>
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>Livro</th>
                        <th class="text-right">Qtd</th>
                        <th class="text-right">Preço unit.</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->book_title ?? $item->book?->name ?? '—' }}</td>
                            <td class="text-right">{{ $item->quantity }}</td>
                            <td class="text-right">{{ number_format($item->unit_price, 2, ',', ' ') }} €</td>
                            <td class="text-right">{{ number_format($item->total, 2, ',', ' ') }} €</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($order->events->isNotEmpty())
            <h3 class="font-semibold text-base mt-6 mb-3">Timeline do pedido</h3>
            <ul class="steps steps-vertical text-sm">
                @foreach($order->events as $event)
                <li class="step step-primary">
                    <span class="font-medium">{{ match($event->event) {
                        'order_created' => 'Pedido criado',
                        'payment_started' => 'Checkout Stripe iniciado',
                        'payment_completed' => 'Pagamento concluído',
                        'payment_expired' => 'Pagamento expirado / cancelado',
                        'order_cancelled' => 'Pedido cancelado',
                        default => $event->event,
                    } }}</span>
                    <span class="text-base-content/60">{{ $event->created_at->format('d/m/Y H:i') }}</span>
                </li>
                @endforeach
            </ul>
            @endif

            <div class="mt-4 pt-4 border-t flex justify-end">
                <a href="{{ route('orders.index') }}" class="btn btn-ghost">← Voltar à lista</a>
            </div>
        </div>
    </div>
</x-app-layout>
