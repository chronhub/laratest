<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('order:customer', ['count' => 500])->everyMinute();

        $schedule->command('order:seed', ['--count' => 500])
            ->withoutOverlapping()
            ->everyMinute();

        $schedule->command('order:prepare-pay')->everyMinute();

        $schedule->command('order:seed-pay')->everyTwoMinutes();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        //require base_path('routes/console.php');
    }
}
