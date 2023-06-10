<?php

declare(strict_types=1);

namespace App\Api;

use Chronhub\Storm\Contracts\Projector\ProjectionQueryScope;
use Chronhub\Storm\Contracts\Projector\ApiProjectionQueryFilter;
use Chronhub\Larastorm\Support\Contracts\ProjectionQueryScopeConnection;

class ApiProjectionQueryScope implements ProjectionQueryScope, ProjectionQueryScopeConnection
{
    public function fromIncludedPosition(int $limit = 500): ApiProjectionQueryFilter
    {
        return new ApiFromIncludedPosition($limit);
    }
}
