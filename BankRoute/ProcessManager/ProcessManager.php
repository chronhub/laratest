<?php

declare(strict_types=1);

namespace BankRoute\ProcessManager;

use Chronhub\Storm\Clock\PointInTime;
use Illuminate\Database\Eloquent\Model;
use Chronhub\Larastorm\Support\Facade\Clock;
use function is_array;
use function serialize;
use function unserialize;

class ProcessManager extends Model
{
    /**
     * @var string
     */
    protected $table = 'process_managers';

    /**
     * @var array<string>
     */
    protected $fillable = ['*'];

    public function start(string $prefix, string $processId, string $nextEvent, array $extra = null): bool
    {
        $data = [
            'prefix' => $prefix,
            'process_id' => $processId,
            'next_event' => $nextEvent,
            'created_at' => Clock::now()->format(Clock::getFormat()),
            'extra' => serialize($extra),
        ];

        return $this->newQuery()->insert($data);
    }

    public function next(string $prefix, string $processId, string $nextEvent, array $extra = null): bool
    {
        if (is_array($extra) && isset($extra[0])) {
            $extra = $extra[0];
        }

        $toUpdate = [
            'next_event' => $nextEvent,
            'updated_at' => (new PointInTime())->now()->format(PointInTime::DATE_TIME_FORMAT),
            'extra' => serialize($extra),
        ];

        $updated = $this->newQuery()
            ->where('prefix', $prefix)
            ->where('process_id', $processId)
            ->update($toUpdate);

        return $updated === 1;
    }

    public function deleteProcess(string $prefix, string $processId): bool
    {
        $deleted = $this->newQuery()
            ->where('prefix', $prefix)
            ->where('process_id', $processId)
            ->delete();

        return $deleted === 1;
    }

    public function findByProcess(string $prefix, string $processId): null|ProcessManager|Model
    {
        return $this->newQuery()
            ->where('prefix', $prefix)
            ->where('process_id', $processId)
            ->first();
    }

    public function processId(): string
    {
        return $this['process_id'];
    }

    public function nextEvent(): string
    {
        return $this['next_event'];
    }

    public function extra(): array
    {
        $data = unserialize($this['extra'], ['allowed_classes' => false]);

        return $data ?? [];
    }
}
