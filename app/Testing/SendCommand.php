<?php

declare(strict_types=1);

namespace App\Testing;

use Chronhub\Storm\Reporter\DomainCommand;
use Chronhub\Storm\Message\HasConstructableContent;

class SendCommand extends DomainCommand
{
    use HasConstructableContent;
}
