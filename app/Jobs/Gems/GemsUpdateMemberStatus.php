<?php

namespace App\Jobs\Gems;

use App\Models\Member\Member;
use App\Services\GemsApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GemsUpdateMemberStatus implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $memberId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($member)
    {
        $this->memberId = is_object($member) ? $member->id : $member;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(GemsApiService $gemsMembersService)
    {
        /** @var Member $member */
        $member = Member::with('gemsApi')
            ->findOrFail($this->memberId);

        $status = Str::of($member->membership_status)
            ->snake()
            ->lower()
            ->toString();

        $gemsSendData = [
            'loyalty_id' => $member->gemsApi->loyal_id,
            'expiry_date' => $member->end_date->toDateString(),
            'card_trn_status' => $status,
            'user_type' => $gemsApi->request['user_type'] ?? null,
        ];

        if ($gemsResponse = $gemsMembersService->updateMemberStatus($gemsSendData)) {
            info($gemsResponse);
        }
    }
}
