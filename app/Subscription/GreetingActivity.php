<?php

declare(strict_types=1);

namespace App\Subscription;

use Psr\Log\LoggerInterface;
use Temporal\Activity\ActivityInterface;

#[ActivityInterface(prefix: 'GreetingActivity.')]
class GreetingActivity
{
    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    public function composeGreeting(string $greeting, string $name): string
    {
        $this->logger->debug('hi'.' '.$name, ['name' => $name]);

        return $greeting.' '.$name;
    }
}
