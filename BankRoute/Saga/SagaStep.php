<?php

declare(strict_types=1);

namespace BankRoute\Saga;

use Generator;
use Chronhub\Storm\Contracts\Reporter\Reporting;

interface SagaStep
{
    public function __invoke(Reporting $message): null|Generator;

    public function compensate(): void;
}
