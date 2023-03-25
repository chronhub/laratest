<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Ordering;

use App\Services\OrderService;
use Illuminate\Console\Command;
use App\Services\QueryOrderService;
use BankRoute\Projection\Order\OrderDetail;
use Illuminate\Database\Eloquent\Collection;

final class RemoveOrderProductCommand extends Command
{
    protected $signature = 'order:remove
                            { order : order id }
                            { product : product id }';

    public function handle(OrderService $orderService, QueryOrderService $queryOrder): int
    {
        $orderId = $this->argument('order');
        $order = $queryOrder->getPendingOrderByIdWithDetails($orderId);

        if ($order === null) {
            $this->error('Order not found');

            return self::FAILURE;
        }

        $orderDetails = $order->getRelation('details');

        if (! $orderDetails instanceof Collection) {
            $this->error('Order is empty');

            return self::FAILURE;
        }

        $productId = $this->argument('product');
        $orderDetail = $orderDetails->where('product_id', $productId)->first();

        if (! $orderDetail instanceof OrderDetail) {
            $this->error('Product not found in order');

            return self::FAILURE;
        }

        $orderService->removeOrderItem($orderId, $productId);

        $this->info("Order $orderId with product $productId removed");

        return self::SUCCESS;
    }
}
