<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Handler;

use Exception;
use App\Report\Order\PayOrder;
use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Order\Service\OrderList;

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
            throw new Exception('Order not found');
        }

        $order->pay();

        $this->orderList->store($order);
    }
}
