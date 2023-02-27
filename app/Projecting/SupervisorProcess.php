<?php

declare(strict_types=1);

namespace App\Projecting;

use Closure;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ExceptionInterface;

final class SupervisorProcess
{
    private Closure $output;

    public function __construct(public readonly Process $process)
    {
    }

    public function start(): self
    {
        $this->process->start();

        return $this;
    }

    public function stop(): void
    {
        $this->sendSignal(SIGINT);
    }

    public function handleOutputUsing(Closure $callback): self
    {
        $this->output = $callback;

        return $this;
    }

    public function __call(string $method, array $arguments)
    {
        return $this->process->{$method}(...$arguments);
    }

    private function sendSignal(int $signal): void
    {
        try {
            $this->process->signal($signal);
        } catch (ExceptionInterface $e) {
            if ($this->process->isRunning()) {
                throw $e;
            }
        }
    }
}
