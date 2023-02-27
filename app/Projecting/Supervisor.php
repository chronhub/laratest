<?php

declare(strict_types=1);

namespace App\Projecting;

use Closure;
use RuntimeException;
use Symfony\Component\Process\Process;
use function sleep;
use function array_values;

final class Supervisor
{
    private array $processes = [];

    private ?Closure $output = null;

    private bool $firstCheck = false;

    private bool $isWorking = false;

    public function __construct(private readonly array $commands,
                                public readonly string $namespace = 'project')
    {
        if (empty($this->commands)) {
            throw new RuntimeException('No commands given.');
        }

        $this->assertUniqueSupervisor();
    }

    public function monitor(): void
    {
        foreach ($this->commands as $command => $name) {
            $this->processes[$name] = $this->createProcess($command);
        }

        $this->start();
    }

    public function stop(): void
    {
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                $process->stop();
            }
        }

        $this->isWorking = false;
    }

    public function check(Closure $output, int $timeout): void
    {
        if (! $this->isWorking) {
            return;
        }

        if ($this->firstCheck) {
            sleep($timeout);
        }

        foreach ($this->processes as $name => $process) {
            $line = ! $process->isRunning() ? 'stopped' : 'running';

            $output->__invoke(Process::OUT, "Projection $name is $line. ".PHP_EOL);
        }

        $output->__invoke(Process::OUT, '*----------------------------------------------------*'.PHP_EOL);

        $this->firstCheck = true;
    }

    public function atLeastOneRunning(): bool
    {
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                return true;
            }
        }

        return false;
    }

    public function handleOutputUsing(Closure $callback): void
    {
        $this->output = $callback;
    }

    public function getStreams(): array
    {
        return array_values($this->commands);
    }

    public function isSupervisorRunning(): bool
    {
        return $this->countSupervisorProcess() === 1;
    }

    private function start(): void
    {
        foreach ($this->processes as $process) {
            if (! $process->isRunning()) {
                $process->start();
            }
        }

        $this->isWorking = true;
    }

    private function createProcess(string $command): SupervisorProcess
    {
        $fullCommand = ProjectorCommandsString::toCommandString($this->namespace, $command);

        $process = Process::fromShellCommandline($fullCommand, base_path())
            ->setTimeout(null)
            ->disableOutput();

        return new SupervisorProcess($process);
    }

    private function countSupervisorProcess(): int
    {
        $processes = Process::fromShellCommandline('ps aux | grep projecting:start | grep -v grep | wc -l')
            ->mustRun()
            ->getOutput();

        return (int) $processes;
    }

    private function assertUniqueSupervisor(): void
    {
        if ($this->countSupervisorProcess() > 1) {
            throw new RuntimeException('There is already a supervisor running.');
        }
    }
}
