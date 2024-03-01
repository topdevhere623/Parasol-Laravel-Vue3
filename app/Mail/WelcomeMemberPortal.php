<?php

namespace App\Mail;

use App\Models\Member\Member;
use App\Models\Plan;
use App\Models\Program;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMemberPortal extends Mailable
{
    use SerializesModels;

    protected ?int $member_id;
    private ?string $token;

    public function __construct($member, ?string $token)
    {
        $this->member_id = is_object($member) ? $member->id : $member;
        $this->token = $token;
    }

    public function build()
    {
        /** @var Member $member */
        $member = Member::with('passkit', 'program')->find($this->member_id);

        $urlParams = ['token' => $this->token];
        if ($member->program_id == Program::ENTERTAINER_SOLEIL_ID) {
            $urlParams['source'] = 'entertainer';
        }

        $url = \URL::member('create-password', $urlParams);

        return $this->view($this->getView($member))
            ->subject($this->getSubject($member))
            ->from($this->getFromEmail($member), $this->getFrom($member))
            ->with('full_name', $member->full_name)
            ->with('url', $url)
            ->with('whatsAppUrl', $member->program->getWhatsappUrl())
            ->with('download_url', optional($member->passKit)->passUrl);
    }

    protected function getView(Member $member): string
    {
        if ($member->program->isProgramSource('hsbc')) {
            if ($member->plan_id === Plan::HSBC_SINGLE_FREE) {
                return 'emails.welcome.hsbc-complimentary-welcome';
            }
            return 'emails.welcome.hsbc-paid-welcome';
        }

        if ($member->program->id == Program::ENTERTAINER_SOLEIL_ID) {
            return 'emails.welcome.entertainer-welcome';
        }

        if ($member->program->id == Program::RAK_BANK_ID) {
            return 'emails.welcome.rak-welcome';
        }

        return 'emails.welcome.default-welcome';
    }

    protected function getSubject(Member $member): string
    {
        return match (true) {
            $member->program->isProgramSource(
                'hsbc'
            ) => 'Create your password & start enjoying HSBC ENTERTAINER soleil!',
            $member->program->id == Program::ENTERTAINER_SOLEIL_ID => 'Create your password & start enjoying soleil!',
            $member->program->id == Program::RAK_BANK_ID => 'Download RAKBANK Elite membership card & set a password',
            default => 'Download adv+ membership card & set a password update',
        };
    }

    protected function getFrom(Member $member): string
    {
        return match (true) {
            $member->program->isProgramSource('hsbc') => 'HSBC ENTERTAINER soleil',
            $member->program->id == Program::ENTERTAINER_SOLEIL_ID => 'ENTERTAINER soleil',
            default => config('mail.from.name'),
        };
    }

    protected function getFromEmail(Member $member)
    {
        return match (true) {
            $member->program->id == Program::ENTERTAINER_SOLEIL_ID => 'entertainersoleil@advplus.ae',
            default => config('mail.from.address'),
        };
    }
}
