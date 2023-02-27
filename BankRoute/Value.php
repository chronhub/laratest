<?php

declare(strict_types=1);

namespace BankRoute;

interface Value
{
    public function sameValueAs(self $aValue): bool;
}
