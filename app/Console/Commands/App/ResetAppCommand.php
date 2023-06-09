<?php

declare(strict_types=1);

namespace App\Console\Commands\App;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use function getenv;
use function is_string;

#[AsCommand(
    name: 'order:setup',
    description: 'Setup shop account with migration and default streams creation,
                  caution, use an empty database as it will clean everything inside'
)]
class ResetAppCommand extends Command
{
    /**
     * List of stream names which will be committed
     * as first commit to the event store for single strategy only
     */
    protected array $streams = ['customer', 'order'];

    public function handle(): int
    {
        if (! $this->isResetConfirmed()) {
            return self::FAILURE;
        }

        $this->dropAllTables();

        // call migrate with env
        $this->call('migrate');

        foreach ($this->streams as $stream) {
            $this->call('stream:create', ['stream' => $stream, 'chronicler' => 'write']);
        }

        return self::SUCCESS;
    }

    protected function dropAllTables(): void
    {
        $this->laravel['db']->connection('pgsql')->getSchemaBuilder()->dropAllTables();
        //$this->laravel['db']->connection('mysql')->getSchemaBuilder()->dropAllTables();
    }

    protected function isResetConfirmed(): bool
    {
        if (! is_string($database = getenv('DB_DATABASE'))) {
            $this->warn('No database specified in env');

            return false;
        }

        if (! $this->confirm('Are you sure you want to drop everything from your database '.$database)) {
            $this->warn('Setup aborted');

            return false;
        }

        return true;
    }
}
