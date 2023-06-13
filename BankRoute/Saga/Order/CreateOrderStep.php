<?php

declare(strict_types=1);

namespace BankRoute\Saga\Order;

use BankRoute\Saga\SagaStep;

final readonly class CreateOrderStep implements SagaStep
{
    public function __construct(private OrderSagaRepository $repository)
    {
    }

    public function execute(): void
    {
        // TODO: Implement execute() method.
    }

    public function compensate(): void
    {
        // TODO: Implement compensate() method.
    }
}
