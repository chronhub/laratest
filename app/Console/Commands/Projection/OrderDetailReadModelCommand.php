<?php

declare(strict_types=1);

namespace App\Console\Commands\Projection;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Chronhub\Storm\Reporter\DomainEvent;
use BankRoute\Model\Order\Event\OrderPaid;
use Chronhub\Larastorm\Support\Facade\Clock;
use Chronhub\Storm\Contracts\Message\Header;
use BankRoute\Model\Order\Event\OrderCanceled;
use Chronhub\Larastorm\Support\Facade\Project;
use BankRoute\Model\Order\Event\OrderItemAdded;
use BankRoute\Model\Order\Event\OrderItemRemoved;
use Chronhub\Storm\Contracts\Projector\Projector;
use BankRoute\Projection\Order\OrderDetailReadModel;
use BankRoute\Model\Order\Event\OrderItemQuantityDecreased;
use BankRoute\Model\Order\Event\OrderItemQuantityIncreased;
use Chronhub\Storm\Contracts\Projector\ReadModelCasterInterface;
use function abs;
use function pcntl_async_signals;

class OrderDetailReadModelCommand extends Command
{
    private Projector $projection;

    protected $signature = 'project:order-detail
                            { projector=default  : projector name }
                            { --signal=1         : dispatch async signal }
                            { --in-background=1  : run in background }';

    public function handle(): int
    {
        if ($this->option('signal') === '1') {
            pcntl_async_signals(true);
        }

        $projectorManager = Project::create($this->argument('projector'));

        $this->projection = $projectorManager->readModel(
            'order_detail',
            $this->laravel[OrderDetailReadModel::class]
        );

        $this->projection->initialize(fn (): array => ['quantity_on_hand' => 0])
            ->fromStreams('order')
            ->whenAny($this->eventHandlers())
            ->withQueryFilter($projectorManager->queryScope()->fromIncludedPosition())
            ->run($this->option('in-background') === '1');

        return self::SUCCESS;
    }

    private function eventHandlers(): callable
    {
        return function (DomainEvent $event, array $state): array {
            /** @var ReadModelCasterInterface $this */
            if ($event instanceof OrderItemAdded
                || $event instanceof OrderItemQuantityIncreased
                || $event instanceof OrderItemQuantityDecreased) {
                $this->readModel()->stack('query', function (Builder $query, string $key, $event): void {
                    $query->insert([
                        'order_id' => $event->orderId()->toString(),
                        'product_id' => $event->productId(),
                        'quantity' => $event->productQuantity(),
                        'price' => $event->productPrice()->value,
                        'created_at' => Clock::format($event->header(Header::EVENT_TIME)),
                    ]);
                }, $event);

                if ($event instanceof OrderItemQuantityDecreased) {
                    $state['quantity_on_hand'] -= abs($event->productQuantity());
                } else {
                    $state['quantity_on_hand'] += $event->productQuantity();
                }
            }

            if ($event instanceof OrderCanceled) {
                $this->readModel()->stack('query', function (Builder $query, string $key, $event): void {
                    $query
                        ->where('order_id', $event->orderId()->toString())
                        ->delete();
                }, $event);

                $state['quantity_on_hand'] -= $event->oldOrderQuantity();
            }

            if ($event instanceof OrderPaid) {
                $state['quantity_on_hand'] -= $event->orderQuantity();
            }

            if ($event instanceof OrderItemRemoved) {
                $this->readModel()->stack('query', function (Builder $query, string $key, OrderItemRemoved $event): void {
                    $query
                        ->where('order_id', $event->orderId()->toString())
                        ->where('product_id', $event->productId())
                        ->delete();
                }, $event);

                $state['quantity_on_hand'] -= $event->productQuantity();
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
