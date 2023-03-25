<?php

declare(strict_types=1);

namespace BankRoute\Infrastructure\Repository;

use BankRoute\Model\Order\Order;
use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Order\Service\OrderList;
use Chronhub\Storm\Contracts\Aggregate\AggregateRoot;
use Chronhub\Storm\Contracts\Aggregate\AggregateRepository;

final readonly class OrderEventStoreRepository implements OrderList
{
    public function __construct(private AggregateRepository $repository)
    {
    }

    public function get(OrderId $orderId): null|Order|AggregateRoot
    {
        return $this->repository->retrieve($orderId);
    }

    public function store(Order $order): void
    {
        $this->repository->store($order);
    }
}
