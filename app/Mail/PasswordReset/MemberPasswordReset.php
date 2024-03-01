<?php

namespace App\Mail\PasswordReset;

use App\Models\Member\Member;
use App\Models\Program;
use Illuminate\Mail\Mailable;

class MemberPasswordReset extends Mailable
{
    protected ?int $member_id;

    protected ?string $url;

    protected string $defaultLogo = 'assets/images/logo_adv-stacker.png';

    protected string $defaultMainColor = '#FFDC7E';

    public function __construct($member, $url)
    {
        $this->member_id = is_object($member) ? $member->id : $member;
        $this->url = $url;
    }

    public function build(): self
    {
        $member = Member::with('program')->findOrFail($this->member_id);

        return $this->view('emails.auth.members.forgot-password')
            ->subject('Password Reset')
            ->from(config('mail.from.address'), $this->getFrom($member))
            ->with('url', $this->url)
            ->with('first_name', $member->first_name)
            ->with('main_color', optional($member->program)->member_portal_main_color ?? $this->defaultMainColor)
            ->with(
                'logo_url',
                optional($member->program)->member_portal_logo
                    ? file_url($member->program, 'member_portal_logo')
                    : asset($this->defaultLogo)
            );
    }

    protected function getFrom(Member $member): string
    {
        return match ($member->program_id) {
            Program::ENTERTAINER_HSBC => 'HSBC ENTERTAINER soleil',
            Program::ENTERTAINER_SOLEIL_ID => 'ENTERTAINER soleil',
            default => config('mail.from.name'),
        };
    }
}
