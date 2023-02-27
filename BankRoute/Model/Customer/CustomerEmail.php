<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer;

use BankRoute\Model\Customer\Exception\CustomerException;
use BankRoute\Value;

final readonly class CustomerEmail implements Value
{
    private function __construct(public string $value)
    {
    }

    public static function fromString(?string $email): self
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new CustomerException('Customer email is invalid');
        }

        return new self($email);
    }

    public function sameValueAs(Value $aValue): bool
    {
        return $aValue instanceof $this && $this->value === $aValue->value;
    }
}
