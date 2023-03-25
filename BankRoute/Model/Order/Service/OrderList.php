<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Service;

use BankRoute\Model\Order\Order;
use BankRoute\Model\Order\OrderId;
use Chronhub\Storm\Contracts\Aggregate\AggregateRoot;

interface OrderList
{
    public function get(OrderId $orderId): null|Order|AggregateRoot;

    public function store(Order $order): void;
}
