<?php

declare(strict_types=1);

namespace App\Api;

use Chronhub\Storm\Contracts\Projector\ApiProjectionQueryFilter;

final class ApiFromIncludedPosition implements ApiProjectionQueryFilter
{
    public function __construct(private readonly int $limit = 500)
    {
    }

    private int $currentPosition = 0;

    private string $streamName;

    public function setCurrentPosition(int $streamPosition): void
    {
        $this->currentPosition = $streamPosition;
    }

    public function setCurrentStreamName(string $streamName): void
    {
        $this->streamName = $streamName;
    }

    public function apply(): callable
    {
        return fn (): array => [
            'stream' => $this->streamName.'s',
            'no' => $this->currentPosition,
            'limit' => $this->limit === 0 ? PHP_INT_MAX : $this->limit,
            'template' => '{+endpoint}/{stream}/from/{no}/limit/{limit}',
        ];
    }
}
