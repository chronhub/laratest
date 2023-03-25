<?php

declare(strict_types=1);

namespace BankRoute\ProcessManager;

use App\Services\OrderService;
use BankRoute\Model\Order\Event\OrderCanceled;

final readonly class RenewOrderOnOrderCanceled
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function onEvent(OrderCanceled $event): void
    {
        $this->orderService->createOrder($event->customerId()->toString());
    }
}
