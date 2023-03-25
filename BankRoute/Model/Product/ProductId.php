<?php

declare(strict_types=1);

namespace BankRoute\Model\Product;

use BankRoute\Value;
use Symfony\Component\Uid\Uuid;
use Chronhub\Storm\Contracts\Message\UniqueId;

final readonly class ProductId implements UniqueId, Value
{
    private function __construct(public Uuid $uuid)
    {
    }

    public static function fromString(string $id): self
    {
        return new self(Uuid::fromString($id));
    }

    public static function create(): self
    {
        return new self(Uuid::v4());
    }

    public function generate(): string
    {
        return $this->uuid->jsonSerialize();
    }

    public function sameValueAs(Value $aValue): bool
    {
        return $aValue instanceof self && $this->uuid->equals($aValue->uuid);
    }

    public function __toString()
    {
        return $this->generate();
    }
}
