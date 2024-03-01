<?php

namespace App\Mail\MembershipRenewal;

use App\Models\Member\MemberPrimary;

class MembershipRenewalReminderExpiredMail extends MembershipRenewalReminderBase
{
    public function build(): self
    {
        $member = MemberPrimary::with('plan', 'package', 'program', 'pendingMembershipRenewal')
            ->findOrFail($this->memberId);

        return $this->view('emails.membership-renewal-reminder.expired.default')
            ->subject("{$member->first_name}, are you thinking about renewing?")
            ->to($member->login_email)
            ->from('memberships@advplus.ae')
            ->with('data', $this->getData($member));
    }
}
