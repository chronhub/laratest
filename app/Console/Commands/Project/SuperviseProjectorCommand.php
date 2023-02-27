<?php

declare(strict_types=1);

namespace App\Console\Commands\Project;

use App\Projecting\Supervisor;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use function max;
use function pcntl_async_signals;

final class SuperviseProjectorCommand extends Command implements SignalableCommandInterface
{
    final public const MIN_CHECK = 10;

    protected $signature = 'projecting:start
                            { --check-every=30 : check projection status every x seconds, min is 10 }';

    private Supervisor $supervisor;

    public function handle(Supervisor $supervisor): void
    {
        $this->supervisor = $supervisor;

        pcntl_async_signals(true);

        $this->supervisor->monitor();

        while ($this->supervisor->atLeastOneRunning()) {
            $this->supervisor->check(function ($type, $line) {
                $this->output->write($line);
            }, max((int) $this->option('check-every'), self::MIN_CHECK));
        }
    }

    public function getSubscribedSignals(): array
    {
        return [SIGINT];
    }

    public function handleSignal(int $signal): void
    {
        $this->warn('Stopping projections...');

        $this->supervisor->stop();
    }
}
