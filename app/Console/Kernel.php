<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\ChangeDriversToTrips;
use App\Console\Commands\OfflineUnAvailableDrivers;
use App\Console\Commands\NotifyDriverDocumentExpiry;
use App\Console\Commands\NotifyDriversForScheduledBidRides;
use App\Console\Commands\AssignDriversForScheduledRides;
use App\Console\Commands\AssignDriversForRegularRides;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ClearDemoDatabase;
use App\Console\Commands\ClearRequestTable;
use App\Console\Commands\ClearOtp;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ChangeDriversToTrips::class,
        NotifyDriverDocumentExpiry::class,
        AssignDriversForScheduledRides::class,
        NotifyDriversForScheduledBidRides::class,
        OfflineUnAvailableDrivers::class,
        AssignDriversForRegularRides::class,
        ClearDemoDatabase::class,
        ClearRequestTable::class,
        ClearOtp::class,

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('drivers:totrip')
                ->everyMinute();
        $schedule->command('assign_drivers:for_regular_rides')
                ->everyMinute();
        $schedule->command('assign_drivers:for_schedule_rides')
                ->everyFiveMinutes();
        $schedule->command('notify_drivers:for_scheduled_bid_rides')
                ->everyMinute();
        $schedule->command('offline:drivers')
                ->everyFiveMinutes();
        $schedule->command('notify:document:expires')
                ->daily();
        $schedule->command('clear:otp')
                ->everyFiveMinutes();
        // $schedule->command('clear:database')
        //          ->daily();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
