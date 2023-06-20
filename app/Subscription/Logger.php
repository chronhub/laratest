<?php

declare(strict_types=1);

namespace App\Subscription;

use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use function sprintf;
use function file_put_contents;

class Logger implements LoggerInterface
{
    use LoggerTrait;

    public function log($level, $message, array $context = []): void
    {
        file_put_contents('php://stderr', sprintf('[%s] %s', $level, $message));
    }
}
