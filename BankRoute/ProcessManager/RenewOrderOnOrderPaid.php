<?php

declare(strict_types=1);

namespace BankRoute\ProcessManager;

use App\Services\OrderService;
use BankRoute\Model\Order\Event\OrderPaid;

final readonly class RenewOrderOnOrderPaid
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function onEvent(OrderPaid $event): void
    {
        // todo make another command as we should kow why we are renewing the order
        $this->orderService->createOrder($event->customerId()->toString());
    }
}
