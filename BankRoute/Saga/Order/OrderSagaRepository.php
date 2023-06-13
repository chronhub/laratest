<?php

declare(strict_types=1);

namespace BankRoute\Saga\Order;

use BankRoute\Saga\SagaRepository;

final class OrderSagaRepository implements SagaRepository
{
    private array $orders = [];

    public function save($data): void
    {
        // TODO: Implement save() method.
    }

    public function delete($data): void
    {
        // TODO: Implement delete() method.
    }
}
