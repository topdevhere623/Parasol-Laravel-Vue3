<?php

namespace App\Jobs\ProgramApi;

use App\Actions\ProgramApi\ProgramSendApiWebhookAction;
use App\Http\Resources\v1\Program\Webhook\Membership\MembershipResource;
use App\Models\Member\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProgramApiSendMemberJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $memberId;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 10;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Member|int $member)
    {
        if (is_object($member)) {
            $this->memberId = $member->id;
        } else {
            $member = Member::find($member);
            $this->memberId = $member->getPrimaryMemberId();
        }

        $this->onQueue('high');
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return self::class.$this->memberId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var Member $member */
        $member = Member::with(
            'partner.clubs',
            'kids',
            'juniors.clubs',
            'program',
            'programApiRequest',
            'clubs',
        )
            ->where('id', $this->memberId)
            ->first();

        if (!$member->programApiRequest || !$member->booking_webhook_sent) {
            return;
        }

        throw_unless($member, new \Exception('Member not found id:'.$this->memberId));

        $programApiRequest = $member?->programApiRequest;

        throw_unless($programApiRequest, new \Exception('Program API not found for member id:'.$member->id));

        $payload = MembershipResource::make($member)->resolve();

        (new ProgramSendApiWebhookAction())->handle($member->program, 'membership', $payload);
    }
}
