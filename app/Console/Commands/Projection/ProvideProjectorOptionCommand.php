<?php

declare(strict_types=1);

namespace App\Console\Commands\Projection;

use RuntimeException;
use Chronhub\Storm\Contracts\Chronicler\QueryFilter;
use Chronhub\Storm\Contracts\Projector\ProjectorManagerInterface;
use Chronhub\Larastorm\Support\Contracts\ProjectionQueryScopeConnection;
use function pcntl_async_signals;

trait ProvideProjectorOptionCommand
{
    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal): void
    {
        $this->projection->stop();
    }

    protected function queryWithLimit(ProjectorManagerInterface $projectorManager): QueryFilter
    {
        $queryScope = $projectorManager->queryScope();

        if (! $queryScope instanceof ProjectionQueryScopeConnection) {
            throw new RuntimeException('Query scope must implement '.ProjectionQueryScopeConnection::class);
        }

        $limit = (int) $this->option('limit');

        return $queryScope->fromIncludedPosition($limit);
    }

    protected function registerSignalHandler(): void
    {
        if ((int) $this->option('signal') === 1) {
            pcntl_async_signals(true);
        }
    }

    protected function keepRunning(): bool
    {
        return (int) $this->option('in-background') === 1;
    }
}
