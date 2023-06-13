<?php

declare(strict_types=1);

namespace BankRoute\Saga;

interface SagaStep
{
    public function execute(): void;

    public function compensate(): void;
}
