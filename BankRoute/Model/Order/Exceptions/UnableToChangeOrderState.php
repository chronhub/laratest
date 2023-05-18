<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Exceptions;

use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Order\OrderState;

class UnableToChangeOrderState extends OrderException
{
    public static function withOrder(OrderId $orderId, OrderState $currentOrderState): self
    {
        return new self("Unable to change state of order $orderId to $currentOrderState->value");
    }
}
