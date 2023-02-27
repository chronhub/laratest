<?php

declare(strict_types=1);

namespace App\Console\Commands\Project;

use App\Projecting\Supervisor;
use Illuminate\Console\Command;
use Chronhub\Larastorm\Support\Facade\Project;

final class CheckProjectorsStatusCommand extends Command
{
    protected $signature = 'projecting:check';

    public function handle(Supervisor $supervisor): void
    {
        $projectorManager = Project::create('emit');

        if (! $supervisor->isSupervisorRunning()) {
            $this->error('Projector Supervisor is not running');

            return;
        }

        $streams = $supervisor->getStreams();

        $headers = ['Stream', 'Status'];
        $rows = [];

        foreach ($streams as $stream) {
            $status = $projectorManager->statusOf($stream);

            $rows[] = [$stream, $status];
        }

        $this->table($headers, $rows);
    }
}
