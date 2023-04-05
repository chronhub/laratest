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
use BankRoute\Model\Order\Event\OrderItemAdded;
use BankRoute\Model\Order\Event\OrderItemRemoved;
use Chronhub\Storm\Contracts\Projector\Projector;
use BankRoute\Projection\Order\OrderViewReadModel;
use BankRoute\Model\Order\Event\OrderItemQuantityDecreased;
use BankRoute\Model\Order\Event\OrderItemQuantityIncreased;
use Chronhub\Storm\Contracts\Projector\ProjectorServiceManager;
use Chronhub\Storm\Contracts\Projector\ReadModelCasterInterface;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Chronhub\Larastorm\Support\Contracts\ProjectionQueryScopeConnection;
use function abs;
use function pcntl_async_signals;

class OrderViewReadModelCommand extends Command implements SignalableCommandInterface
{
    private Projector $projection;

    protected $signature = 'project:order-view
                            { projector=default  : projector name }
                            { --signal=1         : dispatch async signal }
                            { --in-background=1  : run in background }';

    public function handle(ProjectorServiceManager $manager): int
    {
        if ($this->option('signal') === '1') {
            pcntl_async_signals(true);
        }

        $projector = $manager->create($this->argument('projector'));

        $this->projection = $projector->readModel(
            'order_view',
            $this->laravel[OrderViewReadModel::class]
        );

        /** @var ProjectionQueryScopeConnection $queryScope */
        $queryScope = $projector->queryScope();

        $this->projection
            ->initialize(fn (): array => ['in_progress' => 0, 'paid' => 0])
            ->fromStreams('order')
            ->whenAny($this->eventHandlers())
            ->withQueryFilter($queryScope->fromIncludedPositionWithLimit(2000))
            ->run($this->option('in-background') === '1');

        return self::SUCCESS;
    }

    private function eventHandlers(): callable
    {
        return function (DomainEvent $event, array $state): array {
            /** @var ReadModelCasterInterface $this */
            if ($event instanceof OrderCreated) {
                $this->readModel()->stack('query', function (Builder $query, string $key, OrderCreated $event): void {
                    $query->insert([
                        $key => $event->orderId()->toString(),
                        'customer_id' => $event->customerId()->toString(),
                        'quantity' => $event->orderQuantity(),
                        'price' => $event->productPrice()->value,
                        'status' => $event->status()->value,
                        'created_at' => Clock::format($event->header(Header::EVENT_TIME)),
                    ]);
                }, $event);

                $state['in_progress']++;
            }

            if ($event instanceof OrderCanceled) {
                $this->readModel()->stack('query', function (Builder $query, string $key, OrderCanceled $event): void {
                    $query
                        ->where($key, $event->orderId()->toString())
                        ->update([
                            'status' => $event->status()->value,
                            'updated_at' => Clock::format($event->header(Header::EVENT_TIME)),
                        ]);
                }, $event);

                $state['in_progress']--;
            }

            if ($event instanceof OrderItemAdded) {
                $this->readModel()->stack('query', function (Builder $query, string $key, OrderItemAdded $event): void {
                    $query
                        ->where($key, $event->orderId()->toString())
                        ->incrementEach([
                            'quantity' => abs($event->productQuantity()),
                            'price' => $event->productPrice()->value,
                        ], ['updated_at' => Clock::format($event->header(Header::EVENT_TIME))]);
                }, $event);
            }

            if ($event instanceof OrderItemRemoved) {
                $this->readModel()->stack('query', function (Builder $query, string $key, OrderItemRemoved $event): void {
                    $query
                        ->where($key, $event->orderId()->toString())
                        ->decrementEach([
                            'quantity' => abs($event->productQuantity()),
                            'price' => $event->productPrice()->value * abs($event->productQuantity()),
                        ], ['updated_at' => Clock::format($event->header(Header::EVENT_TIME))]);
                }, $event);
            }

            if ($event instanceof OrderItemQuantityIncreased) {
                $this->readModel()->stack('query',
                    function (Builder $query, string $key, OrderItemQuantityIncreased $event): void {
                        $query
                            ->where($key, $event->orderId()->toString())
                            ->incrementEach([
                                'quantity' => $event->productQuantity(),
                                'price' => $event->productPrice()->value,
                            ], ['updated_at' => Clock::format($event->header(Header::EVENT_TIME))]);
                    }, $event);
            }

            if ($event instanceof OrderItemQuantityDecreased) {
                $this->readModel()->stack('query',
                    function (Builder $query, string $key, OrderItemQuantityDecreased $event): void {
                        $query
                            ->where($key, $event->orderId()->toString())
                            ->decrementEach([
                                'quantity' => abs($event->productQuantity()),
                                'price' => $event->productPrice()->value,
                            ], ['updated_at' => Clock::format($event->header(Header::EVENT_TIME))]);
                    }, $event);
            }

            if ($event instanceof OrderModified || $event instanceof OrderPaid) {
                $this->readModel()->stack('query', function (Builder $query, string $key, OrderModified|OrderPaid $event): void {
                    $query
                        ->where($key, $event->orderId()->toString())
                        ->update([
                            'status' => $event->status()->value,
                            'updated_at' => Clock::format($event->header(Header::EVENT_TIME)),
                        ]);
                }, $event);

                if ($event instanceof OrderPaid) {
                    $state['paid']++;
                    $state['in_progress']--;
                }
            }

            // todo delete order and order items when paid ( another projection should handle next process, delivery, refunds, etc. )
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
