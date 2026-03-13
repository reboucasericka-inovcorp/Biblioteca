<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmationMail;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckoutSuccessController extends Controller
{
    /**
     * Confirma o pagamento quando o utilizador regressa da Stripe.
     * Consulta a Checkout Session; se payment_status = "paid", atualiza a Order,
     * regista o evento "payment_confirmed" e envia o email de confirmação.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return view('checkout.success');
        }

        $secret = config('services.stripe.secret');
        if (! $secret) {
            Log::warning('Checkout success: STRIPE_SECRET não definido');
            return view('checkout.success');
        }

        \Stripe\Stripe::setApiKey($secret);

        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
        } catch (\Throwable $e) {
            Log::warning('Checkout success: falha ao obter sessão Stripe', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);
            return view('checkout.success');
        }

        if (($session->payment_status ?? '') !== 'paid') {
            return view('checkout.success');
        }

        $order = Order::with('items')->where('stripe_session_id', $sessionId)->first();

        if (! $order) {
            Log::warning('Checkout success: ordem não encontrada', ['session_id' => $sessionId]);
            return view('checkout.success');
        }

        if ($order->status === Order::STATUS_PAID) {
            return view('checkout.success');
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
                    'event' => 'payment_confirmed',
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Checkout success: falha ao confirmar ordem', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return view('checkout.success');
        }

        $email = $order->user?->email ?? $order->email;
        if ($email) {
            try {
                Mail::to($email)->queue(new OrderConfirmationMail($order->fresh(['items'])));
            } catch (\Throwable $e) {
                Log::warning('Checkout success: falha ao enfileirar email de confirmação', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return view('checkout.success');
    }
}
