<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmation;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StripeWebhookController extends Controller
{
    /**
     * Stripe envia checkout.session.completed quando o pagamento é concluído.
     * Evita reprocessar: se já paid, retorna logo.
     * Marca a ordem como paga e reduz o stock (decremento condicional para evitar race condition).
     */
    public function handle(Request $request)
    {
        $secret = config('services.stripe.webhook_secret');
        if (! $secret) {
            Log::warning('Stripe webhook: STRIPE_WEBHOOK_SECRET não definido');

            return response()->json(['error' => 'Webhook not configured'], 500);
        }

        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature');

        if (! $sig || ! $payload) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig,
                $secret
            );
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook: payload inválido', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook: assinatura inválida', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.expired') {
            $this->handleSessionExpired($event->data->object);

            return response()->json(['received' => true]);
        }

        if ($event->type !== 'checkout.session.completed') {
            return response()->json(['received' => true]);
        }

        $session = $event->data->object;
        $sessionId = $session->id ?? null;

        if (! $sessionId) {
            return response()->json(['received' => true]);
        }

        $order = Order::with('items')->where('stripe_session_id', $sessionId)->first();

        if (! $order) {
            Log::warning('Stripe webhook: ordem não encontrada', ['session_id' => $sessionId]);

            return response()->json(['received' => true]);
        }

        if ($order->status === Order::STATUS_PAID) {
            return response()->json(['received' => true]);
        }

        try {
            DB::transaction(function () use ($order) {
                foreach ($order->items as $item) {
                    $base = Book::query()->where('id', $item->book_id);
                    $stockOk = (clone $base)->where('stock', '>=', $item->quantity)->decrement('stock', $item->quantity);
                    $reservedOk = (clone $base)->where('reserved_stock', '>=', $item->quantity)->decrement('reserved_stock', $item->quantity);
                    if ($stockOk === 0 || $reservedOk === 0) {
                        throw new \RuntimeException(
                            "Stock/reserva insuficiente para book_id {$item->book_id} (ordem #{$order->id})"
                        );
                    }
                }

                $order->update(['status' => Order::STATUS_PAID]);
                OrderEvent::create([
                    'order_id' => $order->id,
                    'event' => OrderEvent::EVENT_PAYMENT_COMPLETED,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Stripe webhook: falha ao processar ordem', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Order processing failed'], 500);
        }

        $email = $order->user?->email ?? $order->email;
        if ($email) {
            try {
                Mail::to($email)->queue(new OrderConfirmation($order->fresh(['items'])));
            } catch (\Throwable $e) {
                Log::warning('Stripe webhook: falha ao enfileirar email de confirmação', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Stripe webhook: ordem paga e stock atualizado', ['order_id' => $order->id]);

        return response()->json(['received' => true]);
    }

    private function handleSessionExpired(object $session): void
    {
        $sessionId = $session->id ?? null;
        if (! $sessionId) {
            return;
        }

        $order = Order::with('items')->where('stripe_session_id', $sessionId)->first();
        if (! $order || $order->status !== Order::STATUS_PENDING) {
            return;
        }

        DB::transaction(function () use ($order) {
            $order->releaseReservedStock();
            $order->update(['status' => Order::STATUS_CANCELLED]);
            OrderEvent::create([
                'order_id' => $order->id,
                'event' => OrderEvent::EVENT_PAYMENT_EXPIRED,
            ]);
        });

        Log::info('Stripe webhook: sessão expirada, reserva devolvida', ['order_id' => $order->id]);
    }
}
