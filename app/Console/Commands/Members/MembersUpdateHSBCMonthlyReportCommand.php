<?php

namespace App\Console\Commands\Members;

use App\Models\Member\Member;
use App\Models\Reports\ReportHSBCMonthlyActiveMember;
use App\Scopes\HSBCComplimentaryPlanScope;
use Illuminate\Console\Command;

class MembersUpdateHSBCMonthlyReportCommand extends Command
{
    protected $signature = 'members:update-hsbc-monthly-report';

    protected $description = 'Update HSBC Active Complimentary Primary Members Report';

    public function handle()
    {
        $preQuery = Member::active()
            ->where('member_type', \App\Models\Member\Member::MEMBER_TYPES['member'])
            ->select('id');

        (new HSBCComplimentaryPlanScope())->apply($preQuery, $preQuery->getModel());

        $count = $preQuery->count();

        if (!$count) {
            $this->info('Nothing to do');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $preQuery->chunkById(100, function ($members) {
            foreach ($members as $member) {
                ReportHSBCMonthlyActiveMember::firstOrCreate([
                    'member_id' => $member->id,
                    'month_year' => now()->format('mY'),
                ]);
            }
        });
        $bar->finish();

        echo "\n";
        return self::SUCCESS;
    }
}
