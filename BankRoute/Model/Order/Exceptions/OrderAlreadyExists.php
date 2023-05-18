<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Exceptions;

use BankRoute\Model\Order\OrderId;

class OrderAlreadyExists extends OrderException
{
    public static function withOrderId(OrderId $orderId): self
    {
        return new self("Order $orderId already exists");
    }
}
