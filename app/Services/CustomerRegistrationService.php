<?php

declare(strict_types=1);

namespace App\Services;

use Chronhub\Storm\Reporter\ReportEvent;
use App\Report\CustomerRegistration\RegisterCustomerStarted;

final readonly class CustomerRegistrationService
{
    public function __construct(private ReportEvent $reportEvent)
    {
    }

    public function startCustomerRegistration(array $payload): void
    {
        $this->reportEvent->relay(new RegisterCustomerStarted($payload));
    }
}
