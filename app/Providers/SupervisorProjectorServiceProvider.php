<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Chronhub\Larastorm\Support\Supervisor\Supervisor;

class SupervisorProjectorServiceProvider extends ServiceProvider
{
    /**
     * todo add console arguments to supervisor
     * key: command name
     * value: projection name
     *
     * @var array<string, string>
     */
    protected array $commands = [
        'customer' => 'customer',
        'customer-premium' => 'customer_premium',
    ];

    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->app->bind(Supervisor::class, function () {
                return new Supervisor(collect($this->commands));
            });
        }
    }
}
