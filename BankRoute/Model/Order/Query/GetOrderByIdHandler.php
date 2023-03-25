<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Query;

use React\Promise\Deferred;
use BankRoute\Projection\Order\OrderProvider;

final readonly class GetOrderByIdHandler
{
    public function __construct(private OrderProvider $orderProvider)
    {
    }

    public function query(GetOrderById $query, Deferred $promise): void
    {
        $promise->resolve(
            $this->orderProvider->orderById($query->content['order_id'])
        );
    }
}
