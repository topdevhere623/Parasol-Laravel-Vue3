<?php

namespace App\Console\Commands\Members;

use App\Actions\Booking\BookingApplyMembershipRenewalAction;
use App\Models\Member\MembershipRenewal;
use Illuminate\Console\Command;

class MembersApplyMembershipRenewalCommand extends Command
{
    protected $signature = 'members:apply-membership-renewal';

    protected $description = 'Applies all membership renewal from related booking';

    public function handle(BookingApplyMembershipRenewalAction $bookingApplyMembershipRenewalAction)
    {
        MembershipRenewal::where('status', MembershipRenewal::STATUSES['awaiting_due_date'])
            ->whereDate('due_date', today())
            ->with('booking.plan', 'booking.paymentMethod')
            ->with('member', fn ($query) => $query->with('partner', 'kids', 'juniors'))
            ->each(
                fn (MembershipRenewal $item) => $bookingApplyMembershipRenewalAction->handle($item)
            );
    }
}
