<?php

declare(strict_types=1);

namespace App\Console\Commands\Projection;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Chronhub\Storm\Reporter\DomainEvent;
use BankRoute\Model\Order\Event\OrderPaid;
use Chronhub\Larastorm\Support\Facade\Clock;
use Chronhub\Storm\Contracts\Message\Header;
use BankRoute\Model\Order\Event\OrderCreated;
use BankRoute\Model\Order\Event\OrderCanceled;
use BankRoute\Model\Order\Event\OrderModified;
use Chronhub\Larastorm\Support\Facade\Project;
use Chronhub\Storm\Contracts\Projector\Projector;
use BankRoute\Projection\Order\CustomerOrdersReadModel;
use Chronhub\Storm\Contracts\Projector\ReadModelProjectorCaster;
use function pcntl_async_signals;

/**
 * @deprecated use when sales and delivery are set
 */
class CustomerOrdersReadModelCommand extends Command
{
    private Projector $projection;

    protected $signature = 'project:customer-orders
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
            'customer_orders',
            $this->laravel[CustomerOrdersReadModel::class]
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
            if ($event instanceof OrderCreated) {
                $this->readModel()->stack('query', function (Builder $query, string $key, OrderCreated $event): void {
                    $query->insert([
                        'customer_id' => $event->customerId()->toString(),
                        'order_id' => $event->orderId()->toString(),
                        'order_status' => $event->status()->value,
                        'created_at' => Clock::format($event->header(Header::EVENT_TIME)),
                    ]);
                }, $event);

                $state['count']++;
            }

            if ($event instanceof OrderModified || $event instanceof OrderPaid || $event instanceof OrderCanceled) {
                $this->readModel()->stack('query', function (Builder $query, string $key, OrderModified|OrderPaid|OrderCanceled $event): void {
                    $query
                        ->where('customer_id', $event->customerId()->toString())
                        ->where('order_id', $event->orderId()->toString())
                        ->update([
                            'order_status' => $event->status()->value,
                            'updated_at' => Clock::format($event->header(Header::EVENT_TIME)),
                        ]);
                }, $event);

                $state['count']++;
            }

            return $state;
        };
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
