<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Handler;

use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Order\Service\OrderList;
use App\Report\Order\MarkOrderAsProcessingPayment;
use BankRoute\Model\Order\Exceptions\OrderNotFound;

final readonly class MarkOrderAsProcessingPaymentHandler
{
    public function __construct(private OrderList $orderList)
    {
    }

    public function command(MarkOrderAsProcessingPayment $command): void
    {
        $orderId = OrderId::fromString($command->orderId());

        $order = $this->orderList->get($orderId);

        if ($order === null) {
            throw OrderNotFound::withOrderId($orderId);
        }

        $order->markAsProcessingPayment();

        $this->orderList->store($order);
    }
}
