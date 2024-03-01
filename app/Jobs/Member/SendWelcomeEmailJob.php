<?php

namespace App\Jobs\Member;

use App\Mail\WelcomeMemberPortal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWelcomeEmailJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected ?int $memberId;

    public $uniqueFor = 4;

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return self::class.$this->memberId;
    }

    public function __construct($member)
    {
        $this->memberId = is_object($member) ? $member->id : $member;
    }

    public function handle()
    {
        \Password::broker('members')->sendResetLink(
            ['id' => $this->memberId],
            function ($user, $token) {
                $token = \Crypt::encrypt([
                    'token' => $token,
                    'login_email' => $user->getEmailForPasswordReset(),
                ]);
                \Mail::to($user->getEmailForPasswordReset())
                    ->send(new WelcomeMemberPortal($user, $token));
            }
        );
    }
}
