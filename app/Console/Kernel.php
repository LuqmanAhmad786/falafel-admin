<?php

namespace App\Console;

use App\Console\Commands\AfterPickupTime;
use App\Console\Commands\BeforePreparationTime;
use App\Console\Commands\birthAlerts;
use App\Console\Commands\ExpireMembership;
use App\Console\Commands\ProcessRefund;
use App\Console\Commands\pushOrderToClover;
use App\Console\Commands\UpdateMenuTiming;
use App\Console\Commands\updateUserMembership;
use App\Console\Commands\UpdateUserPoints;
use App\Console\Commands\ExpireRewardBonus;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        $schedule->command(birthAlerts::class)->daily();
//        $schedule->command(ExpireRewardBonus::class)->daily();
        $schedule->command(BeforePreparationTime::class)->everyMinute();
        $schedule->command(AfterPickupTime::class)->everyMinute();
//        $schedule->command(UpdateUserPoints::class)->everyMinute();
        $schedule->command(ProcessRefund::class)->everyMinute();
        $schedule->command(UpdateMenuTiming::class)->daily();
        $schedule->command(ExpireMembership::class)->daily();
        $schedule->command(updateUserMembership::class)->dailyAt('03:00');
        $schedule->command(pushOrderToClover::class)->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
