<?php

declare(strict_types=1);

namespace App\Console\Commands\Projection;

use Closure;
use Illuminate\Console\Command;
use Chronhub\Storm\Reporter\DomainEvent;
use BankRoute\Model\Order\Event\OrderCreated;
use Chronhub\Storm\Contracts\Projector\Projector;
use BankRoute\Projection\Customer\CustomerReadModel;
use BankRoute\Model\Customer\Event\CustomerRegistered;
use Chronhub\Storm\Contracts\Projector\ProjectorServiceManager;
use Chronhub\Storm\Contracts\Projector\ReadModelCasterInterface;
use Symfony\Component\Console\Command\SignalableCommandInterface;

final class CustomerReadModelCommand extends Command implements SignalableCommandInterface
{
    use ProvideProjectorOptionCommand;

    protected Projector $projection;

    protected $signature = 'project:customer
                            { projector=api_customer : projector name }
                            { --limit=1000           : query filter with limit default 500 or zero for no limit }
                            { --signal=1             : dispatch async signal }
                            { --in-background=1      : run in background }';

    public function handle(ProjectorServiceManager $serviceManager, CustomerReadModel $readModel): int
    {
        $projectorManager = $serviceManager->create($this->argument('projector'));

        $this->projection = $projectorManager->readModel('customer', $readModel);

        $this->registerSignalHandler();

        $this->projection
            ->initialize(fn (): array => ['customers' => 0, 'orders' => 0])
            ->fromStreams('customer', 'order')
            ->whenAny($this->eventHandlers())
            ->withQueryFilter($this->queryWithLimit($projectorManager))
            ->run($this->keepRunning());

        return self::SUCCESS;
    }

    private function eventHandlers(): Closure
    {
        return function (DomainEvent $event, array $state): array {
            /** @var ReadModelCasterInterface $this */
            if ($event instanceof CustomerRegistered) {
                $this->readModel()->stack('recordCustomer', $event);
                $state['customers']++;

                return $state;
            }

            if ($event instanceof OrderCreated) {
                $this->readModel()->stack('updateCustomerOrder', $event);
                $state['orders']++;
            }

            return $state;
        };
    }
}
