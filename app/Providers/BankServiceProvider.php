<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Chronhub\Larastorm\Support\Console\ListMessagerSubscribersCommand;

final class BankServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands(ListMessagerSubscribersCommand::class);
        }
    }
}
