<?php

namespace App\Jobs\Passkit;

use App\Models\Club\Club;
use App\Models\Member\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class PasskitUpdateMemberLiveClubs implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $clubId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($club)
    {
        $this->clubId = is_object($club) ? $club->id : $club;
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return self::class.$this->clubId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $club = Club::findOrFail($this->clubId);

        $club->members()
            ->select(['members.id', 'membership_status', 'members.program_id'])
            ->with('program')->active()->chunk(50, function (Collection $members) {
                $members->each(function (Member $member) {
                    if ($member->hasPasskitAccess()) {
                        PasskitUpdateMember::dispatch($member);
                    }
                });
            });
    }
}
