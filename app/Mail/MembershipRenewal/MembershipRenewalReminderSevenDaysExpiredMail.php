<?php

namespace App\Mail\MembershipRenewal;

use App\Models\Member\MemberPrimary;

class MembershipRenewalReminderSevenDaysExpiredMail extends MembershipRenewalReminderBase
{
    public function build(): self
    {
        $member = MemberPrimary::with('plan', 'package', 'program', 'pendingMembershipRenewal')
            ->findOrFail($this->memberId);

        return $this->view('emails.membership-renewal-reminder.7-days-after-expiry.default')
            ->subject("{$member->first_name}, we’ll miss you!")
            ->to($member->login_email)
            ->from('memberships@advplus.ae')
            ->with('data', $this->getData($member));
    }
}
