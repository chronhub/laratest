<?php

declare(strict_types=1);

namespace BankRoute\ProcessManager;

use App\Report\Order\StartOrder;
use Chronhub\Storm\Reporter\DomainEvent;
use Chronhub\Storm\Reporter\ReportEvent;
use Chronhub\Storm\Reporter\ReportCommand;
use BankRoute\Model\Order\Event\OrderCreated;
use Illuminate\Contracts\Container\Container;
use BankRoute\Model\Order\Handler\StartOrderHandler;

final readonly class OrderProcessManager
{
    final public const NAME = 'pm-order';

    public function __construct(
        private Container $container,
        private RedisProcessManager $processManager,
        private ReportCommand $reportCommand,
        private ReportEvent $reportEvent
    ) {
    }

    public function __invoke(DomainEvent $event): void
    {
        $process = $this->processEvents();

        $process[$event::class]($event);
    }

    public function processEvents(): array
    {
        return [
            StartOrder::class => function (StartOrder $command) {
                $orderId = $command->content['order_id'];

                $this->processManager->start(self::NAME, $orderId, OrderCreated::class);

                $this->processManager->next(self::NAME, $orderId, OrderCreated::class, [
                    $command->toContent(),
                ]);

                $handler = $this->container[StartOrderHandler::class];

                $handler->command($command);
            },

            OrderCreated::class => function (OrderCreated $event) {
                $orderId = $event->content['order_id'];

                $lastEvent = $this->processManager->expect(self::NAME, $orderId);

                if ($lastEvent !== $event::class) {
                    return;
                }

                $this->processManager->next(self::NAME, $orderId, OrderCreated::class, [
                    $event->toContent(),
                ]);
            },
        ];
    }
}
