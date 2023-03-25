<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Ordering;

use BankRoute\PromiseHandler;
use App\Services\OrderService;
use Illuminate\Console\Command;
use App\Services\QueryOrderService;

final class CancelOrderCommand extends Command
{
    use PromiseHandler;

    protected $signature = 'order:cancel { order : order id }';

    public function handle(OrderService $orderService, QueryOrderService $queryOrder): int
    {
        $order = $queryOrder->getOrderById($this->argument('order'));

        if ($order === null) {
            $this->warn('Order not found');

            return self::FAILURE;
        }

        $orderService->cancelOrder($order->orderId(), $order->customerId());

        return self::SUCCESS;
    }
}
