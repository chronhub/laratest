<?php

declare(strict_types=1);

namespace App\Console\Commands\App;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use function getenv;
use function is_string;

#[AsCommand(
    name: 'bank:setup',
    description: 'Setup bank account with migration and default streams creation,
                  caution, use an empty database as it will clean everything inside'
)]
class ResetBankAppCommand extends Command
{
    /**
     * List of stream names which will be committed
     * as first commit to the event store for single strategy only
     */
    protected array $streams = ['customer'];

    public function handle(): int
    {
        if (! $this->isResetConfirmed()) {
            return self::FAILURE;
        }

        $this->getConnection()->getSchemaBuilder()->dropAllTables();

        $this->call('migrate');

        foreach ($this->streams as $stream) {
            $this->call('stream:create', ['stream' => $stream, 'chronicler' => 'write']);
        }

        return self::SUCCESS;
    }

    protected function getConnection(): Connection
    {
        return $this->laravel['db']->connection(getenv('DB_CONNECTION', true));
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
