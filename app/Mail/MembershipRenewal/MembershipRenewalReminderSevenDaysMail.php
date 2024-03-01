<?php

namespace App\Mail\MembershipRenewal;

use App\Models\Member\MemberPrimary;
use App\Models\Plan;

class MembershipRenewalReminderSevenDaysMail extends MembershipRenewalReminderBase
{
    public function build(): self
    {
        $member = MemberPrimary::with('plan', 'package', 'program', 'pendingMembershipRenewal')
            ->findOrFail($this->memberId);

        if ($member->plan->renewal_email_type === Plan::RENEWAL_EMAIL_TYPES['special_offer']) {
            $this->subject($member->first_name.', your membership is almost up!');
        } else {
            $this->subject("{$member->first_name}, your {$member->plan->title} trial membership is almost up!");
        }

        return $this->view(
            'emails.membership-renewal-reminder.7-days.'.\Str::slug(
                $member->plan->renewal_email_type
            )
        )
            ->to($member->login_email)
            ->from('memberships@advplus.ae')
            ->with('data', $this->getData($member));
    }
}
