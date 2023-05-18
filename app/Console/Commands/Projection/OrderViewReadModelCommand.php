<?php

declare(strict_types=1);

namespace App\Console\Commands\Projection;

use Illuminate\Console\Command;
use Chronhub\Storm\Reporter\DomainEvent;
use BankRoute\Model\Order\Event\OrderPaid;
use BankRoute\Model\Order\Event\OrderCreated;
use BankRoute\Model\Order\Event\OrderCanceled;
use BankRoute\Model\Order\Event\OrderModified;
use BankRoute\Model\Order\Event\OrderItemAdded;
use BankRoute\Model\Order\Event\OrderItemRemoved;
use Chronhub\Storm\Contracts\Projector\Projector;
use BankRoute\Projection\Order\OrderViewReadModel;
use BankRoute\Model\Order\Event\OrderItemQuantityDecreased;
use BankRoute\Model\Order\Event\OrderItemQuantityIncreased;
use Chronhub\Storm\Contracts\Projector\ProjectorServiceManager;
use Chronhub\Storm\Contracts\Projector\ReadModelCasterInterface;
use Symfony\Component\Console\Command\SignalableCommandInterface;

class OrderViewReadModelCommand extends Command implements SignalableCommandInterface
{
    use ProvideProjectorOptionCommand;

    protected Projector $projection;

    protected $signature = 'project:order-view
                            { projector=default  : projector name }
                            { limit=1000         : query filter with limit default 1000 or zero for no limit }
                            { --signal=1         : dispatch async signal }
                            { --in-background=1  : run in background }';

    public function handle(ProjectorServiceManager $manager, OrderViewReadModel $readModel): int
    {
        $projector = $manager->create($this->argument('projector'));

        $this->projection = $projector->readModel('order_view', $readModel);

        $this->registerSignalHandler();

        $this->projection
            ->initialize(fn (): array => ['in_progress' => 0, 'paid' => 0])
            ->fromStreams('order')
            ->whenAny($this->eventHandlers())
            ->withQueryFilter($this->queryWithLimit($projector))
            ->run($this->keepRunning());

        return self::SUCCESS;
    }

    private function eventHandlers(): callable
    {
        return function (DomainEvent $event, array $state): array {
            /** @var ReadModelCasterInterface $this */
            if ($event instanceof OrderCreated) {
                $this->readModel()->stack('createOrder', $event);

                $state['in_progress']++;
            }

            if ($event instanceof OrderItemAdded) {
                $this->readModel()->stack('addOrderItem', $event);
            }

            if ($event instanceof OrderItemRemoved) {
                $this->readModel()->stack('removeOrderItem', $event);
            }

            if ($event instanceof OrderItemQuantityIncreased) {
                $this->readModel()->stack('increaseOrderQuantity', $event);
            }

            if ($event instanceof OrderItemQuantityDecreased) {
                $this->readModel()->stack('decreaseOrderQuantity', $event);
            }

            if ($event instanceof OrderModified || $event instanceof OrderPaid || $event instanceof OrderCanceled) {
                $this->readModel()->stack('updateOrderStatus', $event);

                if ($event instanceof OrderCanceled) {
                    $state['in_progress']--;
                }

                if ($event instanceof OrderPaid) {
                    $state['paid']++;
                    $state['in_progress']--;
                }
            }

            // todo delete order and order items when paid ( another projection should handle next process, delivery, refunds, etc. )
            return $state;
        };
    }
}
