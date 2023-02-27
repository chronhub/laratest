<?php

declare(strict_types=1);

namespace App\Console\Commands\Generated;

use Closure;
use Illuminate\Console\Command;
use Chronhub\Storm\Reporter\DomainEvent;
use Chronhub\Larastorm\Support\Facade\Project;
use Chronhub\Storm\Contracts\Projector\Projector;
use Chronhub\Storm\Contracts\Projector\QueryProjectorCaster;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use function pcntl_async_signals;

class FindByEmail extends Command implements SignalableCommandInterface
{
    protected Projector $projection;

    protected $signature = 'project:query-find-by-email
                            { --name=default    : projector name }
                            { --signal=0        : dispatch signal }
                            { --keep-running=0  : run in background }';

    public function handle(): int
    {
        if ($this->option('signal') === '1') {
            pcntl_async_signals(true);
        }

        $projector = Project::create($this->option('name'));

        $this->projection = $projector->projectQuery();

        $this->projection
            ->initialize(fn (): array => ['count' => 0])
            ->fromStreams('customer', 'customer-premium')
            ->whenAny($this->eventHandlers())
            ->withQueryFilter($projector->queryScope()->fromIncludedPosition())
            ->run($this->option('keep-running') === '1');

        return self::SUCCESS;
    }

    public function eventHandlers(): Closure
    {
        return function (DomainEvent $event, array $state): array {
            /** @var QueryProjectorCaster $this */
            $state['count']++;

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
