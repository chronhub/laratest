<?php

declare(strict_types=1);

namespace App\Exceptions;

class InvalidArgumentException extends \InvalidArgumentException implements BankRouteException
{
}
