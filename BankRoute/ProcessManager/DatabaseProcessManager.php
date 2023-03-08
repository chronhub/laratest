<?php

declare(strict_types=1);

namespace BankRoute\ProcessManager;

class DatabaseProcessManager
{
    public function __construct(private readonly ProcessManager $model)
    {
    }

    /**
     * Start process manager
     */
    public function start(string $name, string $processId, string $nextEvent, array $extra = null): void
    {
        $this->model->start($name, $processId, $nextEvent, $extra);
    }

    /**
     * Handle next event
     */
    public function next(string $name, string $processId, string $nextEvent, array $extra = null): void
    {
        $this->model->next($name, $processId, $nextEvent, $extra);
    }

    /**
     * Find next event
     */
    public function expect(string $name, string $processId): ?string
    {
        return $this->current($name, $processId)?->nextEvent();
    }

    /**
     * Current process
     */
    public function current(string $name, string $processId): ?ProcessManager
    {
        return $this->model->findByProcess($name, $processId);
    }

    /**
     * Complete process manager
     */
    public function complete(string $name, string $processId): void
    {
        $this->model->deleteProcess($name, $processId);
    }
}
