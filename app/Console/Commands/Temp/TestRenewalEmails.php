<?php

namespace App\Console\Commands\Temp;

use App\Mail\MembershipRenewal\MembershipRenewalReminderExpiredMail;
use App\Mail\MembershipRenewal\MembershipRenewalReminderSevenDaysExpiredMail;
use App\Mail\MembershipRenewal\MembershipRenewalReminderSevenDaysMail;
use App\Mail\MembershipRenewal\MembershipRenewalReminderThirtyDaysMail;
use App\Models\Member\Member;
use App\Models\Member\MembershipRenewal;
use App\Models\Plan;
use Illuminate\Console\Command;

class TestRenewalEmails extends Command
{
    protected $signature = 'test:renewal-emails';

    public function handle()
    {
        $member = Member::find(168);
        $pendingMembershipRenewal = new MembershipRenewal();

        $plan = $member->plan;
        $plan->renewal_email_type = Plan::RENEWAL_EMAIL_TYPES['corporate'];
        $plan->save();

        $pendingMembershipRenewal->end_date = $member->end_date;
        $pendingMembershipRenewal->member()->associate($member);
        $pendingMembershipRenewal->oldPlan()->associate($member->plan_id);
        $pendingMembershipRenewal->save();

        \Mail::send(new MembershipRenewalReminderThirtyDaysMail($member));
        \Mail::send(new MembershipRenewalReminderSevenDaysMail($member));
        \Mail::send(new MembershipRenewalReminderExpiredMail($member));
        \Mail::send(new MembershipRenewalReminderSevenDaysExpiredMail($member));

        // время костылей!
        sleep(30);

        $plan->renewal_email_type = Plan::RENEWAL_EMAIL_TYPES['special_offer'];
        $plan->save();
        \Mail::send(new MembershipRenewalReminderThirtyDaysMail($member));
        \Mail::send(new MembershipRenewalReminderSevenDaysMail($member));

        return Command::SUCCESS;
    }
}
