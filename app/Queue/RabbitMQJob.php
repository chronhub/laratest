<?php

declare(strict_types=1);

namespace App\Queue;

use Chronhub\Larastorm\Support\Producer\MessageJob;
use function json_decode;
use function unserialize;

final class RabbitMQJob extends \VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob
{
    public function fire(): void
    {
        $payload = json_decode($this->getRawBody(), true, 512, JSON_THROW_ON_ERROR);

        $serialized = $payload['data']['command'];

        $command = unserialize($serialized, [MessageJob::class]);

        if ($command instanceof MessageJob) {
            $command->handle($this->container);
        }

        $this->delete();
    }
}
