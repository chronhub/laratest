<?php

declare(strict_types=1);

namespace App\Queue;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ExceptionInterface;

class ConsumerMq
{
    public function __construct(
        public readonly Process $process,
        public readonly string $name
    ) {
    }

    public function start(): self
    {
        $this->process->start();

        return $this;
    }

    public function stop(): void
    {
        try {
            $this->process->signal(SIGINT);
        } catch (ExceptionInterface $e) {
            if ($this->process->isRunning()) {
                throw $e;
            }
        }
    }
}
