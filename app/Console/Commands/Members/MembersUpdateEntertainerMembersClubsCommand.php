<?php

namespace App\Console\Commands\Members;

use App\Models\Member\Member;
use App\Models\Program;
use Illuminate\Console\Command;

class MembersUpdateEntertainerMembersClubsCommand extends Command
{
    protected $signature = 'members:update-entertainer-members-clubs';

    protected $description = 'Attach Entertainer Members Clubs by plan';

    public function handle()
    {
        $program = Program::whereName('Entertainer')->first();
        if ($program) {
            $preQuery = Member::withoutTrashed()
                ->where('program_id', $program->id)
                ->orderBy('id', 'ASC');

            $count = $preQuery->count();
            $this->info('Attach Entertainer Members Clubs in process... Items-'.$count);
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            $preQuery->chunkById(10, function ($members) use ($bar) {
                foreach ($members as $member) {
                    $clubs = $member->plan->activeClubs()->pluck('id');
                    $member->clubs()->sync($clubs);
                    $bar->advance();
                }
            });

            return self::SUCCESS;
        }

        return self::INVALID;
    }
}
