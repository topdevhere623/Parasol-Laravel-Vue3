<?php

namespace App\Jobs;

use App\Mail\CashbackReward as CashbackRewardMail;
use App\Models\Referral;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class CashbackRewardMailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Referral $referral;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Referral $referral)
    {
        $this->referral = $referral;
    }

    public function handle()
    {
        Mail::to(settings('referral_reward_admin_emails'))
            ->send(new CashbackRewardMail($this->referral));
    }
}
