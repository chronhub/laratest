<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Handler;

use RuntimeException;
use BankRoute\Model\Order\Order;
use App\Report\Order\CancelOrder;
use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Order\Service\OrderList;

final readonly class CancelOrderHandler
{
    public function __construct(private OrderList $orderList)
    {
    }

    public function command(CancelOrder $command): void
    {
        $orderId = OrderId::fromString($command->orderId());

        $order = $this->orderList->get($orderId);

        if (! $order instanceof Order) {
            throw new RuntimeException("Order $orderId not found");
        }

        $order->cancel();

        $this->orderList->store($order);
    }
}
