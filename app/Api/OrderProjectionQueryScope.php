<?php

declare(strict_types=1);

namespace App\Api;

use Chronhub\Storm\Contracts\Projector\ProjectionQueryScope;
use Chronhub\Storm\Contracts\Projector\ProjectionQueryFilter;

class OrderProjectionQueryScope implements ProjectionQueryScope
{
    public function fromIncludedPosition(): ProjectionQueryFilter
    {
        return new ApiOrdersFromIncludedPosition();
    }
}
