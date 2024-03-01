<?php

namespace App\Console\Commands\Checkins;

use App\Models\Club\Checkin;
use App\Models\Club\Club;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use League\Csv\Writer;

class CheckinsDailyReportCommand extends Command
{
    protected $signature = 'checkins:daily-report';

    protected $description = 'Generates a csv report on visits to clubs for yesterday and sends it to e-mail';

    public function handle()
    {
        $date = Carbon::yesterday();

        $csv = Writer::createFromString();
        $csv->setDelimiter(',');

        $checkinsPerDate = Checkin::withoutGlobalScopes()
            ->selectRaw('COUNT(*)')
            ->whereRaw('checkins.club_id = clubs.id')
            ->whereBetween(
                'checkins.checked_in_at',
                [$date->copy()->startOfDay(), $date->copy()->endOfDay()]
            );

        $clubCheckins = Club::withoutGlobalScopes()
            ->selectRaw('clubs.title as club')
            ->selectSub($checkinsPerDate, 'checkins')
            ->orderBy('clubs.title')
            ->groupBy('clubs.id')
            ->get()
            ->toArray();

        $csv->insertAll($clubCheckins);

        Mail::raw(
            "Hello,\n\n\nClubs checkins report for ".$date->format('d F Y'),
            function ($message) use ($date, $csv) {
                $message->to('memberships@advplus.ae')
                    //                    ->cc(['rafik@parasol.me'])
                    ->subject('Daily report adv+ clubs checkin '.$date->format('d F Y'))
                    ->attachData(
                        $csv->toString(),
                        $date->format('Y.m.d').'.csv',
                        ['mime' => 'text/csv']
                    );
            }
        );
    }
}
