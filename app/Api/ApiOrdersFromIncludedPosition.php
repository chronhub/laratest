<?php

declare(strict_types=1);

namespace App\Api;

use Chronhub\Storm\Contracts\Projector\ProjectionQueryFilter;

class ApiOrdersFromIncludedPosition implements ProjectionQueryFilter
{
    public int $currentPosition = 0;

    public function setCurrentPosition(int $streamPosition): void
    {
        $this->currentPosition = $streamPosition;
    }

    public function apply(): callable
    {
        return fn (): array => [
            'page' => 'orders/from',
            'no' => $this->currentPosition,
            'limit' => 1000,
            'template' => '{+endpoint}/{page}/{no}/limit/{limit}',
        ];
    }
}
