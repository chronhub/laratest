<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Handler;

use Exception;
use App\Report\Order\StartOrder;
use BankRoute\Model\Order\Order;
use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Customer\CustomerId;
use BankRoute\Model\Order\Service\OrderList;

final readonly class StartOrderHandler
{
    public function __construct(private OrderList $orderList)
    {
    }

    public function command(StartOrder $command): void
    {
        $orderId = OrderId::fromString($command->orderId());

        $order = $this->orderList->get($orderId);

        if ($order !== null) {
            throw new Exception('Order already exists');
        }

        $order = Order::create($orderId, CustomerId::fromString($command->customerId()));

        $this->orderList->store($order);
    }
}
