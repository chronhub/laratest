<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Query;

use React\Promise\Deferred;
use BankRoute\Projection\Order\OrderProvider;

final readonly class GetModifiedOrdersForPaymentHandler
{
    public function __construct(private OrderProvider $orderProvider)
    {
    }

    public function query(GetModifiedOrdersForPayment $query, Deferred $promise): void
    {
        $promise->resolve(
            $this->orderProvider->modifiedOrdersForPayment()
        );
    }
}
