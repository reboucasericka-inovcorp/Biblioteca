<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                Encomenda #{{ $order->id }}
            </h2>
            <a href="{{ route('orders.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md shadow-sm bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                ← Voltar à lista
            </a>
        </div>
    </x-slot>
    <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Cliente</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $order->customer_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ number_format($order->total, 2, ',', ' ') }} €</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Estado</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $order->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $order->status === 'paid' ? 'Pago' : 'Pendente' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Data</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $order->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>

                @if($order->shipping_address || $order->shipping_city)
                    <h3 class="font-semibold text-gray-900 mb-3">Morada de entrega</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        @if($order->shipping_address)
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Morada</dt>
                                <dd class="mt-1 font-medium text-gray-900">{{ $order->shipping_address }}</dd>
                            </div>
                        @endif
                        @if($order->shipping_city)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Cidade</dt>
                                <dd class="mt-1 font-medium text-gray-900">{{ $order->shipping_city }}</dd>
                            </div>
                        @endif
                        @if($order->shipping_postal_code)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Código postal</dt>
                                <dd class="mt-1 font-medium text-gray-900">{{ $order->shipping_postal_code }}</dd>
                            </div>
                        @endif
                        @if($order->shipping_country)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">País</dt>
                                <dd class="mt-1 font-medium text-gray-900">{{ $order->shipping_country }}</dd>
                            </div>
                        @endif
                    </dl>
                @endif

                <h3 class="font-semibold text-gray-900 mb-3">Itens</h3>
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Livro</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qtd</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Preço unit.</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->book_title ?? $item->book?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($item->unit_price, 2, ',', ' ') }} €</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($item->total, 2, ',', ' ') }} €</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($order->events->isNotEmpty())
                    <h3 class="font-semibold text-gray-900 mt-6 mb-3">Timeline do pedido</h3>
                    <ul class="space-y-2 text-sm">
                        @foreach($order->events as $event)
                            <li class="flex items-start gap-3 py-2 border-l-2 border-gray-200 pl-4 ml-1">
                                <span class="font-medium text-gray-900">{{ match($event->event) {
                                    'order_created' => 'Pedido criado',
                                    'payment_started' => 'Checkout Stripe iniciado',
                                    'payment_completed' => 'Pagamento concluído',
                                    'payment_expired' => 'Pagamento expirado / cancelado',
                                    'order_cancelled' => 'Pedido cancelado',
                                    default => $event->event,
                                } }}</span>
                                <span class="text-gray-500 shrink-0">{{ $event->created_at->format('d/m/Y H:i') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
</x-admin-layout>
