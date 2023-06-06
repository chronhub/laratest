<?php

declare(strict_types=1);

namespace App\Console\Commands\Projection;

use Illuminate\Console\Command;
use Chronhub\Storm\Reporter\DomainEvent;
use App\Api\ApiOrdersFromIncludedPosition;
use BankRoute\Model\Order\Event\OrderPaid;
use BankRoute\Model\Order\Event\OrderCanceled;
use BankRoute\Model\Order\Event\OrderItemAdded;
use BankRoute\Model\Order\Event\OrderItemRemoved;
use Chronhub\Storm\Contracts\Projector\Projector;
use BankRoute\Projection\Order\OrderDetailReadModel;
use BankRoute\Model\Order\Event\OrderItemQuantityDecreased;
use BankRoute\Model\Order\Event\OrderItemQuantityIncreased;
use Chronhub\Storm\Contracts\Projector\ProjectorServiceManager;
use Chronhub\Storm\Contracts\Projector\ReadModelCasterInterface;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use function abs;

class OrderDetailReadModelCommand extends Command implements SignalableCommandInterface
{
    use ProvideProjectorOptionCommand;

    protected Projector $projection;

    protected $signature = 'project:order-detail
                            { projector=api_order      : projector name }
                            { limit=1000         : query filter with limit default 1000 or zero for no limit }
                            { --signal=1         : dispatch async signal }
                            { --in-background=1  : run in background }';

    public function handle(ProjectorServiceManager $serviceManager, OrderDetailReadModel $readModel): int
    {
        $projectorManager = $serviceManager->create($this->argument('projector'));

        $this->projection = $projectorManager->readModel('order_detail', $readModel);

        $this->registerSignalHandler();

        $this->projection
            ->initialize(fn (): array => ['quantity_on_hand' => 0])
            ->fromStreams('order')
            ->whenAny($this->eventHandlers())
            ->withQueryFilter(new ApiOrdersFromIncludedPosition())
            ->run($this->keepRunning());

        return self::SUCCESS;
    }

    private function eventHandlers(): callable
    {
        return function (DomainEvent $event, array $state): array {
            /** @var ReadModelCasterInterface $this */
            if ($event instanceof OrderItemAdded
                || $event instanceof OrderItemQuantityIncreased
                || $event instanceof OrderItemQuantityDecreased) {
                $this->readModel()->stack('recordOrderItem', $event);

                if ($event instanceof OrderItemQuantityDecreased) {
                    $state['quantity_on_hand'] -= abs($event->productQuantity());
                } else {
                    $state['quantity_on_hand'] += $event->productQuantity();
                }
            }

            if ($event instanceof OrderItemRemoved) {
                $this->readModel()->stack('removeOrderItem', $event);

                $state['quantity_on_hand'] -= $event->productQuantity();
            }

            if ($event instanceof OrderCanceled) {
                $this->readModel()->stack('cancelOrder', $event);

                $state['quantity_on_hand'] -= $event->oldOrderQuantity();
            }

            if ($event instanceof OrderPaid) {
                $state['quantity_on_hand'] -= $event->orderQuantity();
            }

            return $state;
        };
    }
}
