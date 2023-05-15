<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Read;

use Illuminate\Console\Command;
use Chronhub\Storm\Reporter\DomainEvent;
use BankRoute\Model\Order\Event\OrderCreated;
use BankRoute\Model\Order\Event\OrderCanceled;
use Chronhub\Larastorm\Support\Facade\Project;
use BankRoute\Model\Order\Event\OrderItemAdded;
use Chronhub\Storm\Contracts\Projector\QueryCasterInterface;
use BankRoute\Model\Order\Event\OrderItemQuantityDecreased;
use BankRoute\Model\Order\Event\OrderItemQuantityIncreased;

final class QueryFirstPendingOrderWithAtLeastFiveInQuantityCommand extends Command
{
    protected $signature = 'order:query-one';

    public function handle(): int
    {
        $projector = Project::create('emit');

        $query = $projector->query();

        $query
            ->initialize(fn (): array => ['orders' => [], 'found' => null])
            ->fromStreams('order')
            ->whenAny(function (DomainEvent $event, array $state): array {
                /** @var QueryCasterInterface $this */
                if ($event instanceof OrderCreated) {
                    $state['orders'] += [$event->orderId()->toString() => 0];

                    return $state;
                }

                if ($event instanceof OrderCanceled) {
                    $state['orders'][$event->orderId()->toString()] = 0;
                    $state['found'] = null;

                    return $state;
                }

                if ($event instanceof OrderItemAdded) {
                    if (isset($state['orders'][$event->orderId()->toString()])) {
                        $state['orders'][$event->orderId()->toString()] += 1;
                    }

                    return $state;
                }

                if ($event instanceof OrderItemQuantityIncreased) {
                    if (isset($state['orders'][$event->orderId()->toString()])) {
                        $state['orders'][$event->orderId()->toString()] += 1;

                        if ($state['orders'][$event->orderId()->toString()] === 5) {
                            //$this->stop();
                            $state['found'] = $event->orderId()->toString();
                        }
                    }

                    return $state;
                }

                if ($event instanceof OrderItemQuantityDecreased) {
                    if (isset($state['orders'][$event->orderId()->toString()])) {
                        $state['orders'][$event->orderId()->toString()] -= 1;
                    }

                    return $state;
                }

                return $state;
            })
            ->withQueryFilter($projector->queryScope()->fromIncludedPosition())
            ->run(false);

        if ($query->getState()['found']) {
            $this->call('order:info', ['order' => $query->getState()['found']]);
        } else {
            $this->warn('no order found with 5 quantity');
        }

        return self::SUCCESS;
    }
}
