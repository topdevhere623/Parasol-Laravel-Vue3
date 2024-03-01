<?php

namespace App\Console\Commands\Checkins;

use App\Models\Club\Checkin;
use App\Models\Club\Club;
use Illuminate\Console\Command;
use ParasolCRM\Activities\Facades\Activity;

class CheckinsDailyCheckoutCommand extends Command
{
    protected $signature = 'checkins:daily-checkout';

    protected $description = 'Daily check-out all checked-ins';

    public function handle()
    {
        $checkins = Checkin::withoutGlobalScopes()
            ->where('status', Checkin::STATUSES['checked_in']);

        $checkins->chunk(100, function ($checkins) {
            $checkins->each->checkout();
        });

        Activity::disable();
        Club::active()->each(fn (Club $club) => $club->save());
        Activity::enable();

        $this->info('Checked-out: '.$checkins->count());
    }
}
