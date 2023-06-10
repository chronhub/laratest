<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Read;

use Closure;
use Illuminate\Console\Command;
use BankRoute\Model\Order\OrderState;
use BankRoute\Model\Order\Event\OrderPaid;
use BankRoute\Model\Order\Event\OrderCreated;
use BankRoute\Model\Order\Event\OrderCanceled;
use Chronhub\Storm\Contracts\Projector\Projector;
use Chronhub\Storm\Contracts\Projector\QueryCasterInterface;
use BankRoute\Model\Order\Event\OrderMarkedAsProcessingPayment;
use Chronhub\Storm\Contracts\Projector\ProjectorServiceManager;
use function json_encode;
use function pcntl_async_signals;

class SumUpOrderCommand extends Command
{
    protected $signature = 'order:summary';

    protected $description = 'Sum up order';

    protected Projector $projection;

    public function handle(ProjectorServiceManager $manager): int
    {
        pcntl_async_signals(true);

        $projector = $manager->create('emit');

        $this->projection = $projector->query();

        $this->projection
            ->initialize(fn (): array => ['created' => 0, 'canceled' => 0, 'processing_payment' => 0, 'paid' => 0])
            ->fromStreams('order')
            ->whenAny($this->eventHandlers())
            ->withQueryFilter($projector->queryScope()->fromIncludedPosition())
            ->run(false);

        $state = $this->projection->getState();

        $this->info('Order sum up:'.PHP_EOL.json_encode($state, JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }

    private function eventHandlers(): Closure
    {
        return function ($event, $state): array {
            /** @var QueryCasterInterface $this */
            if ($event instanceof OrderCreated) {
                $state['created']++;

                return $state;
            }

            if ($event instanceof OrderCanceled) {
                $state['canceled']++;

                $oldStatus = $event->toContent()['old_order_status'];
                if ($oldStatus === OrderState::ProcessingPayment->value) {
                    $state['processing_payment']--;
                }
            }

            if ($event instanceof OrderMarkedAsProcessingPayment) {
                $state['processing_payment']++;
            }

            if ($event instanceof OrderPaid) {
                $state['paid']++;
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
