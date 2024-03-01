<?php

namespace App\Console;

use App\Console\Commands\Checkins\CheckinsCheckoutCommand;
use App\Console\Commands\Checkins\CheckinsDailyCheckoutCommand;
use App\Console\Commands\Checkins\ZeroGravityCommand;
use App\Console\Commands\Members\MemberActivateByStartDateCommand;
use App\Console\Commands\Members\MembersUpdateExpiredStatusCommand;
use App\Console\Commands\PassportRegenerateClientsSecrets;
use App\Console\Commands\Plecto\PlectoSendBackofficeUsers;
use App\Jobs\PaymentMethods\CheckTabbyPendingTransactionsJob;
use App\Models\Club\Club;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use ParasolCRM\Activities\Facades\Activity;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        CheckinsDailyCheckoutCommand::class,
        CheckinsCheckoutCommand::class,
        ZeroGravityCommand::class,
        MembersUpdateExpiredStatusCommand::class,
        MemberActivateByStartDateCommand::class,
        PassportRegenerateClientsSecrets::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('checkins:daily-checkout')->daily();
        $schedule->command('checkins:checkout')->everyFiveMinutes();
        $schedule->command('checkins:daily-report')->dailyAt('09:00')
            ->when(fn () => app()->isProduction());

        $schedule->command('zero-gravity:traffic')->days(
            [Schedule::FRIDAY, Schedule::SATURDAY, Schedule::SUNDAY, Schedule::MONDAY]
        )->at('00:01');

        // Update membership expiry (with check renewal). After apply membership renewal for already expired members
        $schedule->command('members:update-expired-status')->dailyAt('00:00')
            ->after(fn () => \Artisan::call('members:apply-membership-renewal'));

        $schedule->command('members:activate-by-start-date')->daily();
        $schedule->command('members:update-hsbc-monthly-report')->dailyAt('01:00');
        $schedule->command('members:process-membership-renewal-reminders')
            ->when(fn () => app()->isProduction())
            ->dailyAt('09:00');

        $schedule->command('coupons:update-expired-status')->daily();
        $schedule->command('offers:update-expired-status')->daily();

        $schedule->command('leads:update-standby-status')->everyFiveMinutes();

        $schedule->command('membershipprocess:update-overdue-status')->daily();
        $schedule->command('partner:contract-observer-trigger')->daily();
        $schedule->command('partner:tranche-observer-trigger')->daily();

        $schedule->command('auth:clear-resets')->everyFifteenMinutes();
        $schedule->command('passport:purge')->weeklyOn(Schedule::MONDAY);

        // run on 08:00 1st, 3rd, 7th day of each month
        $schedule->command('payments:process-recurring')->cron('0 8 1,3,7 * *')
            ->when(fn () => app()->isProduction());

        $schedule->job(CheckTabbyPendingTransactionsJob::class, 'high')
            ->when(fn () => config('services.tabby.check_pending_payments'))
            ->everyFiveMinutes();

        $schedule->call(function () {
            Activity::disable();

            $club = Club::find(1);
            $club->is_always_red = true;
            $club->save();

            Activity::enable();
        })->dailyAt('17:00');

        $schedule->call(function () {
            Activity::disable();

            $club = Club::find(1);
            $club->is_always_red = false;
            $club->traffic = Club::TRAFFICS['green'];
            $club->save();

            Activity::enable();
        })->dailyAt('20:00');

        $schedule->command('instagram:get-feed')
            ->hourly()
            ->when(fn () => app()->isProduction());

        $schedule->command('program:generate-club-documents')
            ->dailyAt('04:00')
            ->when(fn () => app()->isProduction());

        $schedule->command(PlectoSendBackofficeUsers::class)
            ->weekly()
            ->when(fn () => app()->isProduction());
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
