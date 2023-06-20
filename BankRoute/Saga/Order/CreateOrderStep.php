<?php

declare(strict_types=1);

namespace BankRoute\Saga\Order;

use RuntimeException;
use BankRoute\Saga\SagaStep;
use App\Report\Order\StartOrder;
use Chronhub\Storm\Contracts\Reporter\Reporting;
use BankRoute\Model\Order\Handler\StartOrderHandler;

final class CreateOrderStep implements SagaStep
{
    private string $token;

    public function __construct(private readonly OrderSagaRepository $repository)
    {
    }

    public function __invoke(Reporting $message): null
    {
        if ($message instanceof StartOrder) {
            $this->token = $message->orderId();

            $this->repository->save($this->token);

            $handler = app(StartOrderHandler::class);

            $handler->command($message);

            return;
        }

        throw new RuntimeException('Invalid message');
    }

    public function compensate(): void
    {
        $this->repository->delete($this->token);
    }
}
