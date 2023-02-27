<?php

declare(strict_types=1);

namespace App\Providers;

use App\Projecting\Supervisor;
use Illuminate\Support\ServiceProvider;

class SupervisorProjectorServiceProvider extends ServiceProvider
{
    /**
     * key: console command
     * value: stream name
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
                return new Supervisor($this->commands);
            });
        }
    }
}
