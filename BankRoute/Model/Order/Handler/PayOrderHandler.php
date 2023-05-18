<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Handler;

use App\Report\Order\PayOrder;
use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Order\Service\OrderList;
use BankRoute\Model\Order\Exceptions\OrderNotFound;

final readonly class PayOrderHandler
{
    public function __construct(private OrderList $orderList)
    {
    }

    public function command(PayOrder $command): void
    {
        $orderId = OrderId::fromString($command->orderId());

        $order = $this->orderList->get($orderId);

        if ($order === null) {
            throw OrderNotFound::withOrderId($orderId);
        }

        $order->pay();

        $this->orderList->store($order);
    }
}
