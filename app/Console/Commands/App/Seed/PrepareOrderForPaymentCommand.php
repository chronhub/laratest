<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Seed;

use BankRoute\PromiseHandler;
use App\Services\OrderService;
use Illuminate\Console\Command;
use App\Services\QueryOrderService;
use BankRoute\Projection\Order\OrderView;

class PrepareOrderForPaymentCommand extends Command
{
    use PromiseHandler;

    protected $signature = 'order:seed-prepare-pay';

    public function handle(QueryOrderService $query, OrderService $orderService): int
    {
        $orders = $query->getModifiedOrdersForPayment();

        $orders->each(
            fn (OrderView $order) => $orderService->markOrderAsProcessingPayment(
                $order->orderId(), $order->customerId()
            )
        );

        $count = $orders->count();

        if ($count === 0) {
            $this->warn('No order to prepare for payment');

            return self::FAILURE;
        }

        $this->info("$count orders prepared for payment");

        return self::SUCCESS;
    }
}
