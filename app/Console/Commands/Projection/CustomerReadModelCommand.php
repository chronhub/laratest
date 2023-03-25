<?php

declare(strict_types=1);

namespace App\Console\Commands\Projection;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Chronhub\Storm\Reporter\DomainEvent;
use Chronhub\Larastorm\Support\Facade\Clock;
use Chronhub\Storm\Contracts\Message\Header;
use BankRoute\Model\Order\Event\OrderCreated;
use Chronhub\Larastorm\Support\Facade\Project;
use Chronhub\Storm\Contracts\Projector\Projector;
use BankRoute\Projection\Customer\CustomerReadModel;
use BankRoute\Model\Customer\Event\CustomerRegistered;
use Chronhub\Storm\Contracts\Projector\ReadModelProjectorCaster;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use function pcntl_async_signals;

final class CustomerReadModelCommand extends Command implements SignalableCommandInterface
{
    private Projector $projection;

    protected $signature = 'project:customer
                            { projector=default  : projector name }
                            { --signal=1         : dispatch async signal }
                            { --in-background=1  : run in background }';

    public function handle(): int
    {
        if ($this->option('signal') === '1') {
            pcntl_async_signals(true);
        }

        $projectorManager = Project::create($this->argument('projector'));

        $this->projection = $projectorManager->projectReadModel(
            'customer',
            $this->laravel[CustomerReadModel::class]
        );

        $this->projection->initialize(fn (): array => ['count' => 0])
            ->fromStreams('customer', 'order')
            ->whenAny($this->eventHandlers())
            ->withQueryFilter($projectorManager->queryScope()->fromIncludedPosition())
            ->run($this->option('in-background') === '1');

        return self::SUCCESS;
    }

    private function eventHandlers(): callable
    {
        return function (DomainEvent $event, array $state): array {
            /** @var ReadModelProjectorCaster $this */
            if ($event instanceof CustomerRegistered) {
                $this->readModel()->stack('query', function (Builder $query, string $key, CustomerRegistered $event): void {
                    $query->insert([
                        $key => $event->aggregateId()->toString(),
                        'email' => $event->customerEmail()->value,
                        'status' => $event->customerStatus()->value,
                        'created_at' => Clock::format($event->header(Header::EVENT_TIME)),
                    ]);
                }, $event);

                $state['count']++;
            }

            if ($event instanceof OrderCreated) {
                $this->readModel()->stack('query', function (Builder $query, string $key, OrderCreated $event): void {
                    $query
                        ->where($key, $event->customerId()->toString())
                        ->update([
                            'current_order_id' => $event->orderId()->toString(),
                        ]);
                }, $event);

                $state['count']++;
            }

            return $state;
        };
    }

    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal): void
    {
        $this->projection->stop();
    }
}
