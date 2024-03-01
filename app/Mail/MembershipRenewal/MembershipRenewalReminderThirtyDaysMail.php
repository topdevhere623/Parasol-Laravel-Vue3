<?php

namespace App\Mail\MembershipRenewal;

use App\Models\Member\MemberPrimary;
use App\Models\Plan;

class MembershipRenewalReminderThirtyDaysMail extends MembershipRenewalReminderBase
{
    public function build(): self
    {
        $member = MemberPrimary::with('plan', 'package', 'program', 'pendingMembershipRenewal')
            ->findOrFail($this->memberId);

        $template = $member->plan->renewal_email_type == Plan::RENEWAL_EMAIL_TYPES['corporate'] ? 'corporate' : 'default';

        return $this->view('emails.membership-renewal-reminder.30-days.'.$template)
            ->subject($member->first_name.', will you stay with us for another year?')
            ->to($member->login_email)
            ->from('memberships@advplus.ae')
            ->with('data', $this->getData($member));
    }
}
