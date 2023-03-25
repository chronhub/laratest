<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Ordering;

use BankRoute\PromiseHandler;
use App\Services\OrderService;
use Illuminate\Console\Command;
use App\Services\QueryOrderService;
use BankRoute\Projection\Order\OrderDetail;
use Illuminate\Database\Eloquent\Collection;

final class DecreaseOrderProductQuantityCommand extends Command
{
    use PromiseHandler;

    protected $signature = 'order:decrease
                            { order : order id }
                            { product : product id }';

    public function handle(OrderService $orderService, QueryOrderService $orderQuery): int
    {
        // we only query the order detail for mimic an api error called

        $orderId = $this->argument('order');
        $order = $orderQuery->getPendingOrderByIdWithDetails($orderId);

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

        $orderService->decreaseOrderItemQuantity($orderId, $productId);

        $operation = $order->quantity() - 1 === 0 ? 'removed' : 'decreased';

        $this->info("Order $orderId with product $productId quantity $operation");

        return self::SUCCESS;
    }
}
