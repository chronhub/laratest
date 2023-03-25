<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Seed;

use Illuminate\Console\Command;
use App\Services\QueryOrderService;
use BankRoute\Projection\Order\OrderView;
use function random_int;

final class PayOrCancelOrderCommand extends Command
{
    protected $signature = 'order:seed-pay-or-cancel { --rate=70 : payment success rate in percent, default 70% }';

    public function handle(QueryOrderService $queryOrder): int
    {
        $rate = (int) $this->option('rate');

        $orders = $queryOrder->getPendingOrdersWithDetails();

        if ($orders->isEmpty()) {
            $this->warn('No orders pending');

            return self::FAILURE;
        }

        // todo need status process payment to reject all others event which can happen later
        // Order should be in status "payment in progress" or "payment failed" or "payment success"
        // Payment should be handle synchronously

        $orders->each(function (OrderView $order) use ($rate) {
            $payOrReset = random_int(0, 100) <= $rate;

            $this->call('order:'.($payOrReset ? 'pay' : 'cancel'), ['order' => $order->orderId()]);
        });

        $this->info("{$orders->count()} orders processed");

        return self::SUCCESS;
    }
}
