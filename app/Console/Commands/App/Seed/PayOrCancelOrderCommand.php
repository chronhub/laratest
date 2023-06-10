<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Seed;

use Illuminate\Console\Command;
use App\Services\QueryOrderService;
use BankRoute\Projection\Order\OrderView;
use function random_int;

final class PayOrCancelOrderCommand extends Command
{
    protected $signature = 'order:seed-pay-or-cancel
                            { --rate=70 : payment success rate in percent, default 70% }';

    public function handle(QueryOrderService $queryOrder): int
    {
        $rate = (int) $this->option('rate');

        $orders = $queryOrder->getPreparedOrdersForPaymentWithDetails();

        if ($orders->isEmpty()) {
            $this->warn('No orders prepared for payment');

            return self::FAILURE;
        }

        $orders->each(fn (OrderView $order) => $this->callOrder($order->orderId(), $rate));

        $this->info("{$orders->count()} orders processed");

        return self::SUCCESS;
    }

    private function callOrder(string $orderId, int $rate): void
    {
        $payOrReset = random_int(0, 100) <= $rate;

        $this->call('order:'.($payOrReset ? 'pay' : 'cancel'), ['order' => $orderId]);
    }
}
