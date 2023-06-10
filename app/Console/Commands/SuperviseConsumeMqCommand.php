<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Closure;
use App\Queue\SupervisorMq;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use function pcntl_async_signals;

final class SuperviseConsumeMqCommand extends Command implements SignalableCommandInterface
{
    const MIN_CHECK_EVERY = 5;

    protected $signature = 'supervisor:consume-mq';

    protected SupervisorMq $supervisor;

    private array $consumers = [
        'customer' => [
            'connection' => 'rabbitmq',
            'workers' => 4,
        ],
        'order' => [
            'connection' => 'rabbitmq',
            'workers' => 10,
        ],
        'payment' => [
            'connection' => 'rabbitmq',
            'workers' => 6,
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

        while ($this->supervisor->atLeastOneRunning()) {
            $this->supervisor->check($this->usingOutput(), self::MIN_CHECK_EVERY);
        }
    }

    protected function usingOutput(): ?Closure
    {
        //        if ($this->option('output') !== '1') {
        //            return null;
        //        }

        return function ($type, $line): void {
            $this->output->write($line);
        };
    }
}
