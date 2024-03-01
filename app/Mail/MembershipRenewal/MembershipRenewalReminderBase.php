<?php

namespace App\Mail\MembershipRenewal;

use App\Models\Member\Member;
use App\Models\Plan;
use App\Models\Program;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;

class MembershipRenewalReminderBase extends Mailable implements ShouldQueue
{
    protected int $memberId;

    public function __construct(Member|int $member)
    {
        $this->memberId = is_object($member) ? $member->id : $member;
    }

    public function getData(Member $member): array
    {
        return [
            'first_name' => $member->first_name,
            'membership_number' => $member->member_id,
            'renewal_url' => $member->pendingMembershipRenewal->renewal_url,
            'header_color' => $member->program->member_portal_main_color,
            'header_logo' => $member->program->website_logo ? file_url(
                $member->program,
                'website_logo',
                'original'
            ) : asset('assets/images/logo-sm.svg'),
            'program_name' => $member->program->public_name,
            'end_date' => app_date_format($member->end_date),
            'package_description' => $member->package->description,
            'show_header_powered' => $member->plan->renewal_email_type == Plan::RENEWAL_EMAIL_TYPES['corporate'] && $member->program->id != Program::ENTERTAINER_SOLEIL_ID,
            'whatsapp_url' => $member->program->getWhatsappUrl(),
        ];
    }
}
