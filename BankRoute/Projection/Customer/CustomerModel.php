<?php

declare(strict_types=1);

namespace BankRoute\Projection\Customer;

use BankRoute\Projection\ReadModelTable;
use BankRoute\Projection\ReadOnlyEloquentModel;

final class CustomerModel extends ReadOnlyEloquentModel
{
    protected $table = ReadModelTable::CUSTOMER;
}
