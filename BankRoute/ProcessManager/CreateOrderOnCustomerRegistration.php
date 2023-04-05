<?php

declare(strict_types=1);

namespace BankRoute\ProcessManager;

use App\Services\OrderService;
use BankRoute\Model\Customer\Event\CustomerRegistered;

final readonly class CreateOrderOnCustomerRegistration
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function onEvent(CustomerRegistered $event): void
    {
        $this->orderService->createOrder($event->aggregateId()->toString());
    }
}
