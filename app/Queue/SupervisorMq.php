<?php

declare(strict_types=1);

namespace App\Queue;

use Closure;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use function sleep;
use function usleep;
use function str_replace;

class SupervisorMq
{
    private Collection $processes;

    private bool $isWorking = false;

    private false $firstCheck = false;

    public function __construct()
    {
        $this->processes = new Collection();
    }

    public function monitor(array $commands): void
    {
        foreach ($commands as $queue => $consumer) {
            $numWorkers = $consumer['workers'] ?? 1;

            while ($numWorkers !== 0) {
                $process = $this->createProcess($consumer['connection'], $queue, $numWorkers);
                $this->processes->push($process);
                $numWorkers--;
            }
        }

        $this->start();
    }

    public function stop(): void
    {
        $this->processes->each(
            function (ConsumerMq $supervised): void {
                if ($supervised->process->isStarted()) {
                    $supervised->stop();
                }
            }
        );

        // help check with output to display final status
        while ($this->atLeastOneRunning()) {
            usleep(100);
        }

        $this->isWorking = false;
    }

    public function check(?Closure $output = null, int $timeout = 10): void
    {
        if (! $this->isWorking) {
            return;
        }

        if (! $this->firstCheck) {
            sleep($timeout);
        }

        $this->processes
            ->when($output !== null)
            ->each(
                function (ConsumerMq $supervised) use ($output): void {
                    if ($supervised->process->isTerminated()) {
                        $supervised->process->start();
                        $output->__invoke(Process::OUT, "Restart consumer $supervised->name".PHP_EOL);
                    } else {
                        $result = $supervised->process->getIncrementalOutput();
                        if ($result !== '') {
                            $output->__invoke(Process::OUT, $result.PHP_EOL);
                        }
                    }
                });

        $this->firstCheck = false;
    }

    public function atLeastOneRunning(): bool
    {
        return $this->processes
            ->skipUntil(fn (ConsumerMq $supervised) => $supervised->process->isRunning())
            ->isNotEmpty();
    }

    public function isWorking(): bool
    {
        return $this->isWorking;
    }

    private function start(): void
    {
        $this->processes->each(
            function (ConsumerMq $supervised): void {
                if (! $supervised->process->isStarted()) {
                    $supervised->start();
                }
            }
        );

        $this->isWorking = true;
    }

    private function createProcess(string $connection, string $queue, int $num): ConsumerMq
    {
        $command = $this->commandAsString($connection, $queue);

        $process = Process::fromShellCommandline($command, base_path())
            ->setTimeout(null)
            ->enableOutput();

        $name = $connection.'-'.$queue.'-'.$num;

        return new ConsumerMq($process, $name);
    }

    private function commandAsString(string $connection, string $queue): string
    {
        $command = 'exec @php artisan rabbitmq:consume @connection --queue=@queue --tries=1 --sleep=0 --backoff=1 --memory=256 --max-jobs=1000 --timeout=0';

        return str_replace(
            ['@php', '@connection', '@queue'],
            [$this->phpPath(), $connection, $queue],
            $command
        );
    }

    public function phpPath(): string
    {
        $binary = new PhpExecutableFinder();

        return $binary->find(false);
    }
}
