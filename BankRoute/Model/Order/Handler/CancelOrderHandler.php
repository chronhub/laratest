<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Handler;

use App\Report\Order\CancelOrder;
use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Order\Service\OrderList;
use BankRoute\Model\Order\Exceptions\OrderNotFound;

final readonly class CancelOrderHandler
{
    public function __construct(private OrderList $orderList)
    {
    }

    public function command(CancelOrder $command): void
    {
        $orderId = OrderId::fromString($command->orderId());

        $order = $this->orderList->get($orderId);

        if ($order === null) {
            throw OrderNotFound::withOrderId($orderId);
        }

        $order->cancel();

        $this->orderList->store($order);
    }
}
