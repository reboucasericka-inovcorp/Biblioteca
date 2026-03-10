<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReleaseExpiredOrderReservations extends Command
{
    protected $signature = 'orders:release-expired-reservations {--minutes=10 : Minutos após os quais considerar reserva expirada}';

    protected $description = 'Liberta stock reservado de pedidos pendentes há mais de N minutos (anti-overselling).';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $cutoff = now()->subMinutes($minutes);

        $orders = Order::with('items')
            ->where('status', Order::STATUS_PENDING)
            ->where('created_at', '<', $cutoff)
            ->get();

        $released = 0;
        foreach ($orders as $order) {
            DB::transaction(function () use ($order, &$released) {
                $order->releaseReservedStock();
                $order->update(['status' => Order::STATUS_CANCELLED]);
                OrderEvent::create([
                    'order_id' => $order->id,
                    'event' => OrderEvent::EVENT_PAYMENT_EXPIRED,
                    'meta' => ['reason' => 'scheduled_expiry'],
                ]);
                $released++;
            });
        }

        if ($released > 0) {
            $this->info("Reservas expiradas libertadas: {$released} pedido(s).");
        }

        return self::SUCCESS;
    }
}
