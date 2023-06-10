<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Ordering;

use BankRoute\PromiseHandler;
use App\Services\OrderService;
use Illuminate\Console\Command;
use App\Services\QueryOrderService;
use BankRoute\Projection\Order\OrderView;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'order:pay',
    description: 'Pay order by order id'
)]
final class PayOrderCommand extends Command
{
    use PromiseHandler;

    protected $signature = 'order:pay { order : order id }';

    public function handle(QueryOrderService $orderQuery, OrderService $orderService): int
    {
        $order = $orderQuery->getOrderById($this->argument('order'));

        if (! $order instanceof OrderView) {
            $this->warn('Order not found');

            return self::FAILURE;
        }

        $orderService->payOrder($order->orderId(), $order->customerId());

        return self::SUCCESS;
    }
}
