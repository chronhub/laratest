<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Handler;

use App\Report\Order\StartOrder;
use BankRoute\Model\Order\Order;
use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Customer\CustomerId;
use BankRoute\Model\Order\Service\OrderList;
use BankRoute\Model\Order\Exceptions\OrderAlreadyExists;

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
            throw OrderAlreadyExists::withOrderId($orderId);
        }

        $order = Order::create($orderId, CustomerId::fromString($command->customerId()));

        $this->orderList->store($order);
    }
}
