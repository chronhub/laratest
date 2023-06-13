<?php

declare(strict_types=1);

namespace BankRoute\Saga;

interface SagaRepository
{
    public function save($data): void;

    public function delete($data): void;
}
