<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
// use App\Console\Commands\ReportUserRequest;
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('report:userreqcount')->hourly();

        // if(env('APP_ENV') === 'local'){
        //     // $schedule->command('report:userreqcount')->everySecond();
        //     $schedule->job(ReportUserRequest::class)->everyMinute();
        // }else{
        //     $schedule->exec('php /var/www/RestAPI_Bearish/artisan report:userreqcount')->everySecond();
        //     // $schedule->job(ReportUserRequest::class)->everyMinute();
        // }
        // $schedule->command(ReportUserRequest::class)->everyMinute();
        // $schedule->job(ReportUserRequest::class)->everyMinute(); // env('APP_ENV') === 'local'

        // $schedule->job(ReportUserRequest::class)->everyMinute();
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
