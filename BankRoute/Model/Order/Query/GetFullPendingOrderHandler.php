<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Query;

use React\Promise\Deferred;
use BankRoute\Model\Order\OrderState;
use BankRoute\Projection\Order\OrderProvider;

final readonly class GetFullPendingOrderHandler
{
    public function __construct(private OrderProvider $orderProvider)
    {
    }

    public function query(GetFullPendingOrder $query, Deferred $promise): void
    {
        $promise->resolve(
            $this->orderProvider->fullOrderByIdAndStatus(
                $query->content['order_id'],
                OrderState::Pending->value,
                OrderState::Modified->value,
            )
        );
    }
}
