<?php

declare(strict_types=1);

namespace BankRoute\ProcessManager;

use App\Services\OrderService;
use App\Report\Customer\Signup\CustomerSignupCompleted;

final readonly class CreateOrderOnCustomerRegistration
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function onEvent(CustomerSignupCompleted $event): void
    {
        $this->orderService->createOrder($event->content['id']);
    }
}
