<?php

namespace App\Mail;

use App\Models\Referral;
use Illuminate\Mail\Mailable;

class CashbackReward extends Mailable
{
    private Referral $referral;
    protected string $mainColor = '#FFDC7E';
    protected string $logo = 'assets/images/logo_adv-stacker.png';

    public function __construct(Referral $referral)
    {
        $this->referral = $referral;
    }

    public function build(): self
    {
        $member = $this->referral->member;
        $usedMember = $this->referral->usedMember;

        return $this
            ->from(config('mail.from.address'))
            ->subject(sprintf('Referral reward chosen (%s)', $this->makeReadable(Referral::REWARDS[$this->referral->reward])))
            ->view('emails.cashback-reward')
            ->with('mainColor', $this->mainColor)
            ->with('logoUrl', $this->logo)
            ->with('data', [
                'referral_id' => $this->referral->id,
                'reward' => [
                    'type' => $this->makeReadable(Referral::REWARDS[$this->referral->reward]),
                    'status' => $this->makeReadable(Referral::REWARD_STATUSES[$this->referral->reward_status]),
                ],
                'member' => [
                    'id' => $member->id,
                    'number' => $member->member_id,
                ],
                'referral_member' => [
                    'id' => $usedMember->id ?? 0,
                    'number' => $usedMember->member_id ?? 0,
                ],
                'notes' => $this->referral->notes,
            ]);
    }

    private function makeReadable(string $str): string
    {
        return str_replace('_', ' ', ucfirst($str));
    }
}
