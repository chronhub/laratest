<?php

declare(strict_types=1);

namespace BankRoute\ProcessManager;

use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\Repository;

readonly class RedisProcessManager
{
    private Repository $store;

    public function __construct()
    {
        $this->store = Cache::store('redis');
    }

    public function start(string $name, string $processId, string $nextEvent, array $extra = null): void
    {
        $this->store->put($name.':'.$processId, [
            'next_event' => $nextEvent,
            'extra' => $extra,
        ], 60 * 60 * 24 * 7);
    }

    public function next(string $name, string $processId, string $nextEvent, array $extra = null): void
    {
        $this->store->put($name.':'.$processId, [
            'next_event' => $nextEvent,
            'extra' => $extra,
        ], 60 * 60 * 24 * 7);
    }

    public function expect(string $name, string $processId): ?string
    {
        return $this->store->get($name.':'.$processId)['next_event'] ?? null;
    }

    public function current(string $name, string $processId): ?array
    {
        return $this->store->get($name.':'.$processId);
    }

    public function complete(string $name, string $processId): void
    {
        $this->store->forget($name.':'.$processId);
    }
}
