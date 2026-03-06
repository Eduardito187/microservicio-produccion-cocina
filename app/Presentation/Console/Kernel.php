<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Presentation\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

/**
 * @class Kernel
 * @package App\Presentation\Console
 */
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('outbox:publish')->everyMinute()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
