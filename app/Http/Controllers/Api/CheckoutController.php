<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\OrderItem;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    /** Quantidade máxima de unidades por pedido (limite de reserva anti-abuso). */
    private const MAX_QUANTITY_PER_ORDER = 10;

    /**
     * Valida o carrinho, cria Order (pending) + OrderItems, depois cria sessão Stripe.
     * Preços e totais vêm sempre do backend (Book no banco); Stripe nunca recebe dados do request.
     */
    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.book_id' => 'required|integer|exists:books,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            $msg = $validator->errors()->first() ?? 'Dados do carrinho inválidos.';
            return ApiResponse::error($msg, 400);
        }

        $items = $request->input('items');
        $totalQuantity = array_sum(array_map(fn ($i) => (int) ($i['quantity'] ?? 0), $items));
        if ($totalQuantity > self::MAX_QUANTITY_PER_ORDER) {
            return ApiResponse::error('Limite de '.self::MAX_QUANTITY_PER_ORDER.' unidades por pedido.', 400);
        }

        $orderTotal = 0.0;
        $validatedLines = [];

        foreach ($items as $item) {
            $book = Book::findOrFail($item['book_id']);

            if (! $book->is_active) {
                return ApiResponse::error("Livro indisponível: {$book->name}", 400);
            }

            $quantity = (int) $item['quantity'];
            $available = $book->available_stock;
            if ($quantity > $available) {
                return ApiResponse::error("Stock insuficiente para: {$book->name} (disponível: {$available})", 400);
            }

            $price = (float) $book->price;
            $discountPct = (float) ($book->discount ?? 0) / 100;
            $unitPrice = round($price * (1 - $discountPct), 2);
            $total = round($unitPrice * $quantity, 2);
            $orderTotal += $total;

            $bookCover = $book->cover ? 'storage/'.$book->cover : $book->thumbnail_url;

            $validatedLines[] = [
                'book' => $book,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $total,
                'book_cover' => $bookCover,
            ];
        }

        $secret = config('services.stripe.secret');
        if (! $secret) {
            return ApiResponse::error('Pagamento não configurado. Defina STRIPE_SECRET em .env.', 503);
        }

        $user = $request->user();
        $order = DB::transaction(function () use ($user, $orderTotal, $validatedLines) {
            $order = Order::create([
                'user_id' => $user?->id,
                'email' => $user?->email ?? request()->input('email'),
                'total' => round($orderTotal, 2),
                'status' => Order::STATUS_PENDING,
            ]);

            OrderEvent::create([
                'order_id' => $order->id,
                'event' => OrderEvent::EVENT_ORDER_CREATED,
            ]);

            foreach ($validatedLines as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $line['book']->id,
                    'book_title' => $line['book']->name,
                    'book_cover' => $line['book_cover'] ?? null,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'total' => $line['total'],
                ]);
                $line['book']->increment('reserved_stock', $line['quantity']);
            }

            return $order;
        });

        $order->load('items');
        $lineItems = $this->buildStripeLineItemsFromOrder($order);
        $calculatedTotal = $order->items->sum(fn ($item) => (float) $item->unit_price * (int) $item->quantity);
        if (abs($calculatedTotal - (float) $order->total) > 0.01) {
            $order->releaseReservedStock();
            return ApiResponse::error('Inconsistência no total do pedido.', 400);
        }

        \Stripe\Stripe::setApiKey($secret);

        try {
            $session = \Stripe\Checkout\Session::create([
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => url('/checkout/success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => url('/cart'),
            ]);
        } catch (\Throwable $e) {
            $order->releaseReservedStock();

            return ApiResponse::error('Erro ao criar sessão de pagamento: '.$e->getMessage(), 502);
        }

        $order->update(['stripe_session_id' => $session->id]);
        OrderEvent::create([
            'order_id' => $order->id,
            'event' => OrderEvent::EVENT_PAYMENT_STARTED,
        ]);

        return ApiResponse::success([
            'url' => $session->url,
            'session_id' => $session->id,
        ]);
    }

    /**
     * Constrói line_items para o Stripe apenas com dados do backend (Book no banco).
     * Preço nunca vem do request — proteção contra alteração de preço no frontend.
     */
    private function buildStripeLineItemsFromOrder(Order $order): array
    {
        $lineItems = [];
        foreach ($order->items as $item) {
            $book = Book::findOrFail($item->book_id);
            $unitPrice = (float) $book->price * (1 - (float) ($book->discount ?? 0) / 100);
            $unitPrice = round($unitPrice, 2);
            $imageUrl = $item->book_cover
                ? (filter_var($item->book_cover, FILTER_VALIDATE_URL) ? $item->book_cover : asset($item->book_cover))
                : null;
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item->book_title,
                        'images' => $imageUrl ? [$imageUrl] : [],
                    ],
                    'unit_amount' => (int) round($unitPrice * 100),
                ],
                'quantity' => $item->quantity,
            ];
        }
        return $lineItems;
    }
}
