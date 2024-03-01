<?php

namespace App\Console\Commands\Members;

use App\Actions\Member\GetOrCreatePendingRenewalMemberAction;
use App\Jobs\Lead\CreateFromMembershipRenewalLeadJob;
use App\Mail\MembershipRenewal\MembershipRenewalReminderExpiredMail;
use App\Mail\MembershipRenewal\MembershipRenewalReminderSevenDaysExpiredMail;
use App\Mail\MembershipRenewal\MembershipRenewalReminderSevenDaysMail;
use App\Mail\MembershipRenewal\MembershipRenewalReminderThirtyDaysMail;
use App\Models\Member\MemberPrimary;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class MembersProcessMembershipRenewalRemindersCommand extends Command
{
    protected $signature = 'members:process-membership-renewal-reminders';

    protected $description = 'Update membership renewals and send reminder emails';

    public function handle()
    {
        MemberPrimary::doesntHave('awaitingDueDateMembershipRenewal')
            ->with('pendingMembershipRenewal')
            ->has('plan.renewalPackage')
            ->active()
            ->whereDate('end_date', today()->addDays(30))
            ->chunk(100, function (Collection $items) {
                $items->each(function (MemberPrimary $member) {
                    $pendingMembershipRenewal = $this->getMembershipRenewal($member);
                    if (!$pendingMembershipRenewal->is_30_days_email_sent) {
                        \Mail::send(new MembershipRenewalReminderThirtyDaysMail($member));
                        $pendingMembershipRenewal->is_30_days_email_sent = true;
                        $pendingMembershipRenewal->save();
                        CreateFromMembershipRenewalLeadJob::dispatch($pendingMembershipRenewal);
                    }
                });
            });

        MemberPrimary::doesntHave('awaitingDueDateMembershipRenewal')
            ->has('plan.renewalPackage')
            ->with('pendingMembershipRenewal')
            ->active()
            ->whereDate('end_date', today()->addDays(7))
            ->chunk(100, function (Collection $items) {
                $items->each(function (MemberPrimary $member) {
                    $pendingMembershipRenewal = $this->getMembershipRenewal($member);
                    if (!$pendingMembershipRenewal->is_7_days_email_sent) {
                        \Mail::send(new MembershipRenewalReminderSevenDaysMail($member));
                        $pendingMembershipRenewal->is_7_days_email_sent = true;
                        $pendingMembershipRenewal->save();
                    }
                });
            });

        MemberPrimary::doesntHave('awaitingDueDateMembershipRenewal')
            ->withWhereHas('pendingMembershipRenewal')
            ->has('plan.renewalPackage')
            ->expired()
            ->whereDate('end_date', today())
            ->chunk(100, function (Collection $items) {
                $items->each(function (MemberPrimary $member) {
                    $pendingMembershipRenewal = $this->getMembershipRenewal($member);
                    if (!$pendingMembershipRenewal->is_expired_email_sent) {
                        \Mail::send(new MembershipRenewalReminderExpiredMail($member));
                        $pendingMembershipRenewal->is_expired_email_sent = true;
                        $pendingMembershipRenewal->save();
                    }
                });
            });

        MemberPrimary::doesntHave('awaitingDueDateMembershipRenewal')
            ->withWhereHas('pendingMembershipRenewal')
            ->has('plan.renewalPackage')
            ->expired()
            ->whereDate('end_date', today()->subDays(7))
            ->chunk(100, function (Collection $items) {
                $items->each(function (MemberPrimary $member) {
                    $pendingMembershipRenewal = $this->getMembershipRenewal($member);
                    if (!$pendingMembershipRenewal->is_7_days_expired_email_sent) {
                        \Mail::send(new MembershipRenewalReminderSevenDaysExpiredMail($member));
                        $pendingMembershipRenewal->is_7_days_expired_email_sent = true;
                        $pendingMembershipRenewal->save();
                    }
                });
            });
    }

    protected function getMembershipRenewal(MemberPrimary $member)
    {
        return (new GetOrCreatePendingRenewalMemberAction())->handle($member);
    }
}
