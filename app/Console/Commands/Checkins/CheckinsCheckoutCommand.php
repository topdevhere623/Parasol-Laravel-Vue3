<?php

namespace App\Console\Commands\Checkins;

use App\Models\Club\Checkin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckinsCheckoutCommand extends Command
{
    protected $signature = 'checkins:checkout';

    protected $description = 'Check-out members from clubs where field Hourly Auto Check-out run every 5 minutes';

    public function handle()
    {
        $checkins = Checkin::withoutGlobalScopes()
            ->select('checkins.*')
            ->where('checkins.status', Checkin::STATUSES['checked_in'])
            ->where('clubs.auto_checkout_after', '>', 0)
            ->whereRaw(DB::raw('checkins.checked_in_at <= DATE_SUB(NOW(), INTERVAL clubs.auto_checkout_after MINUTE)'))
            ->leftJoin('clubs', 'clubs.id', '=', 'checkins.club_id');

        $checkins->chunk(100, function ($checkins) {
            $checkins->each->checkout();
        });

        $this->info('Checked-out: '.$checkins->count());
    }
}
