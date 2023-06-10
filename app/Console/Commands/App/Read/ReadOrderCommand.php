<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Read;

use Str;
use Closure;
use Illuminate\Console\Command;
use BankRoute\Model\Order\Event\OrderPaid;
use BankRoute\Model\Order\Event\OrderCreated;
use BankRoute\Model\Order\Event\OrderCanceled;
use BankRoute\Model\Order\Event\OrderItemAdded;
use BankRoute\Model\Order\Event\OrderItemRemoved;
use Chronhub\Storm\Contracts\Projector\Projector;
use BankRoute\Model\Order\Event\OrderItemQuantityDecreased;
use BankRoute\Model\Order\Event\OrderItemQuantityIncreased;
use Chronhub\Storm\Contracts\Projector\QueryCasterInterface;
use Chronhub\Storm\Contracts\Projector\ProjectorServiceManager;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use function usleep;
use function str_split;
use function method_exists;
use function pcntl_async_signals;

final class ReadOrderCommand extends Command implements SignalableCommandInterface
{
    protected $signature = 'order:read
                                { order      : order id }
                                { --timer=30 : timer in seconds to avoid infinite loop }';

    protected Projector $projection;

    public function handle(ProjectorServiceManager $manager): int
    {
        pcntl_async_signals(true);

        $orderId = $this->argument('order');

        $projector = $manager->create('emit');

        $this->projection = $projector->query();

        $this->projection
            ->initialize(fn (): array => ['found' => false])
            ->fromStreams('order')
            ->whenAny($this->eventHandlers($orderId))
            ->withQueryFilter($projector->queryScope()->fromIncludedPosition())
            ->withTimer((int) $this->option('timer'))
            ->run(true);

        if (! $this->projection->getState()['found']) {
            $this->warn("Order $orderId not found in time");
        }

        return self::SUCCESS;
    }

    private function eventHandlers(string $orderId): Closure
    {
        $print = function (string $event): void {
            foreach (str_split(Str::slug($event, ' ')) as $char) {
                $this->output->write($char);
                usleep(20000);
            }

            $this->output->newLine();
        };

        return function ($event, $state) use ($orderId, $print): array {
            if (! method_exists($event, 'orderId')) {
                $print('event rejected: '.class_basename($event));

                return $state;
            }

            /** @var QueryCasterInterface $this */
            if ($event->orderId()->toString() !== $orderId) {
                return $state;
            }

            if ($event instanceof OrderCreated) {
                $print("Order $orderId created");

                $state['found'] = true;

                return $state;
            }

            if ($event instanceof OrderItemAdded) {
                $print('Product added');

                return $state;
            }

            if ($event instanceof OrderItemRemoved) {
                $print('Product removed');

                return $state;
            }

            if ($event instanceof OrderItemQuantityIncreased) {
                $print('Product quantity increased');

                return $state;
            }

            if ($event instanceof OrderItemQuantityDecreased) {
                $print('Product quantity decreased');

                return $state;
            }

            if ($event instanceof OrderCanceled) {
                $print("Order $orderId canceled");

                $this->stop();
            }

            if ($event instanceof OrderPaid) {
                $print("Order $orderId paid");

                $this->stop();
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
        $this->line('Stopping projection...');

        $this->projection->stop();
    }
}
