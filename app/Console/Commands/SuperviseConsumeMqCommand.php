<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Closure;
use App\Queue\SupervisorMq;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use function usleep;
use function microtime;
use function pcntl_async_signals;

final class SuperviseConsumeMqCommand extends Command implements SignalableCommandInterface
{
    const MIN_CHECK_EVERY = 10;

    const RESTART_ALL_EVERY = 300;

    protected $signature = 'supervisor:consume-mq';

    protected SupervisorMq $supervisor;

    private array $consumers = [
        'customer' => [
            'connection' => 'rabbitmq',
            'workers' => 2,
        ],
        'order' => [
            'connection' => 'rabbitmq',
            'workers' => 2,
        ],
        'payment' => [
            'connection' => 'rabbitmq',
            'workers' => 2,
        ],
    ];

    public function handle(SupervisorMq $supervisor): int
    {
        $this->supervisor = $supervisor;

        pcntl_async_signals(true);

        $this->loop();

        return self::SUCCESS;
    }

    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal): void
    {
        $this->supervisor->stop();
    }

    protected function loop(): void
    {
        if (! $this->supervisor->isWorking()) {
            $this->supervisor->monitor($this->consumers);
        }

        $start = microtime(true);

        do {
            $this->supervisor->check($this->usingOutput(), self::MIN_CHECK_EVERY);
        } while ($this->supervisor->atLeastOneRunning() && (microtime(true) - $start) < self::RESTART_ALL_EVERY);

        $this->warn('Restarting all consumers');

        $this->supervisor->stop();

        usleep(10000);

        $this->loop();
    }

    protected function usingOutput(): ?Closure
    {
        return function ($type, $line): void {
            $this->output->write($line);
        };
    }
}
