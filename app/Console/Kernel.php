<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

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
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@nationalSchool')
                ->dailyAt('00:25');

        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@compositeRisk')
                ->dailyAt('00:45');

        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@actionplanImpactRefConsultant')
                ->dailyAt('00:50');

        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@importAdminusers')
                ->dailyAt('00:45');

        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@acplanReviewdateReminder')
               ->dailyAt('00:05');

        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@cronVerifiedTutor')
                ->dailyAt('00:10');

        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@transitionEmail')
                ->dailyAt('00:05');

        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@dataEntered')
                ->dailyAt('00:10');

//        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@assIncompleteMail')
//                ->hourly()
//                ->between('00:45', '00:50');

//        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@checkReminderProfroma')
//                ->dailyAt('00:20');

//        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@unsubscribeProforma')
//                ->daily();

        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@clearTmpStorage')
                ->monthlyOn(1, '02:00');

        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@alertSendEmail')
                ->weeklyOn('5', '05:00');

        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@automatedGlobal')
                ->hourly()
                ->between('00:45', '00:50');

        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@automatedTables')
                ->hourly()
                ->between('00:45', '00:50');

        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@meanScoresYearInt')
                ->dailyAt('00:25');
        
        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@polarbias')
                ->dailyAt('00:35');
        
        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@checkNewWondeData')
                 ->cron('2-59 0 * * *');
        
        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@updateWondeCronStatus')
                ->dailyAt('00:01');
        
        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@getExecutiveSummaryReport')
                ->monthlyOn(1, '02:30');
        
        $schedule->call('App\Http\Controllers\Common\Checkers\Cron_controller@getExportData')
                ->dailyAt('02:45');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

}
