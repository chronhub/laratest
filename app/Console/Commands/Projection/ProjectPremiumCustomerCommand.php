<?php

declare(strict_types=1);

namespace App\Console\Commands\Projection;

use Closure;
use InvalidArgumentException;
use Illuminate\Console\Command;
use Chronhub\Storm\Reporter\DomainEvent;
use Chronhub\Larastorm\Support\Facade\Project;
use Chronhub\Storm\Contracts\Projector\Projector;
use BankRoute\Model\Customer\Event\CustomerRegistered;
use Chronhub\Storm\Contracts\Projector\ProjectorManager;
use Chronhub\Storm\Contracts\Projector\ProjectionQueryFilter;
use Chronhub\Storm\Contracts\Projector\PersistentProjectorCaster;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Chronhub\Larastorm\Support\Contracts\ProjectionQueryScopeConnection;
use function str_starts_with;
use function pcntl_async_signals;

final class ProjectPremiumCustomerCommand extends Command implements SignalableCommandInterface
{
    private Projector $projection;

    protected $signature = 'project:customer-premium
                            { projector=emit     : projector name }
                            { --signal=1         : dispatch async signal }
                            { --in-background=1  : run in background }';

    public function handle(): int
    {
        if ($this->option('signal') === '1') {
            pcntl_async_signals(true);
        }

        $projectorManager = Project::create($this->argument('projector'));

        $this->projection = $projectorManager->projectProjection('customer_premium');

        $this->projection->initialize(fn (): array => ['count' => 0])
            ->withQueryFilter($this->queryFilter($projectorManager))
            ->fromStreams('customer')
            ->whenAny($this->eventHandlers())
            ->run($this->option('in-background') === '1');

        return self::SUCCESS;
    }

    private function eventHandlers(): Closure
    {
        return function (DomainEvent $event, array $state): array {
            /** @var PersistentProjectorCaster $this */
            if ($event instanceof CustomerRegistered) {
                $customerEmail = $event->customerEmail()->value;

                if (str_starts_with($customerEmail, 's')) {
                    $this->emit($event);
                    $state['count']++;
                }
            }

            return $state;
        };
    }

    private function queryFilter(ProjectorManager $manager): ProjectionQueryFilter
    {
        $queryScope = $manager->queryScope();

        if (! $queryScope instanceof ProjectionQueryScopeConnection) {
            throw new InvalidArgumentException('QueryScope is not an instance of ProjectionQueryScopeConnection');
        }

        return $queryScope->fromIncludedPositionWithLimit(2000);
    }

    public function getSubscribedSignals(): array
    {
        return [SIGINT];
    }

    public function handleSignal(int $signal): void
    {
        $this->projection->stop();
    }
}
