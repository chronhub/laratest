<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use BankRoute\Model\Customer\CustomerCollection;

final class HomeController
{
    public function __invoke(CustomerCollection $customers): string
    {
        return 'ok';
    }
}
