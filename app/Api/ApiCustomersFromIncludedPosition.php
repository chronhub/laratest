<?php

declare(strict_types=1);

namespace App\Api;

use Chronhub\Storm\Contracts\Projector\ProjectionQueryFilter;

final class ApiCustomersFromIncludedPosition implements ProjectionQueryFilter
{
    public int $currentPosition = 0;

    private string $streamName;

    public function setCurrentPosition(int $streamPosition): void
    {
        $this->currentPosition = $streamPosition;
    }

    public function setStreamName(string $streamName): void
    {
        $this->streamName = $streamName;
    }

    public function apply(): callable
    {
        if ($this->streamName === 'customer') {
            return fn (): array => [
                'page' => 'customers/from',
                'no' => $this->currentPosition,
                'limit' => 1000,
                'template' => '{+endpoint}/{page}/{no}/limit/{limit}',
            ];
        }

        return fn (): array => [
            'page' => 'orders/from',
            'no' => $this->currentPosition,
            'limit' => 1000,
            'template' => '{+endpoint}/{page}/{no}/limit/{limit}',
        ];
    }
}
