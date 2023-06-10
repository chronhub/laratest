<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Read;

use Closure;
use Illuminate\Console\Command;
use Chronhub\Storm\Reporter\DomainEvent;
use Chronhub\Storm\Contracts\Message\EventHeader;
use Chronhub\Storm\Contracts\Projector\Projector;
use Chronhub\Storm\Contracts\Projector\ProjectorServiceManager;
use function count;
use function json_encode;
use function pcntl_async_signals;

class ReadGapCommand extends Command
{
    protected $signature = 'order:gap {stream_name}';

    protected Projector $projection;

    public function handle(ProjectorServiceManager $manager): int
    {
        pcntl_async_signals(true);

        $projector = $manager->create('emit');

        $this->projection = $projector->query();

        $this->projection
            ->initialize(fn (): array => ['gap' => [], 'previous' => 0, 'current' => 0])
            ->fromStreams($this->argument('stream_name'))
            ->whenAny($this->eventHandlers())
            ->withQueryFilter($projector->queryScope()->fromIncludedPosition())
            ->run(false);

        $state = $this->projection->getState();

        $this->info('count gaps:'.(count($state['gap'])));
        $this->info('gap:'.PHP_EOL.json_encode($state, JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }

    private function eventHandlers(): Closure
    {
        return function (DomainEvent $event, $state): array {
            if ($state['previous'] === 0 || $state['current'] === $state['previous']) {
                $state['previous'] = $event->header(EventHeader::INTERNAL_POSITION);

                return $state;
            }

            if ($state['current'] === 0 || $state['current'] === $state['previous']) {
                $state['current'] = $event->header(EventHeader::INTERNAL_POSITION);

                return $state;
            }

            $gap = $state['current'] - $state['previous'];

            if ($gap > 1) {
                $state['gap'][] = $gap;
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
