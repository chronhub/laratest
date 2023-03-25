<?php

declare(strict_types=1);

namespace App\Services;

use Chronhub\Storm\Reporter\ReportEvent;
use App\Report\Customer\Signup\CustomerSignupStarted;

final readonly class CustomerSignUpService
{
    public function __construct(private ReportEvent $reportEvent)
    {
    }

    public function start(array $payload): void
    {
        $this->reportEvent->relay(new CustomerSignupStarted($payload));
    }
}
