<?php

declare(strict_types=1);

namespace App\Subscription;

use Illuminate\Support\ServiceProvider;

class TemporalServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $dispatcher = $this->app[GreetWorkerFactory::class];
        $dispatcher->serve(null);
    }
}
