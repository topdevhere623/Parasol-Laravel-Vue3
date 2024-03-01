<?php

namespace App\Actions\Booking;

use App\Jobs\Gems\GemsSendBooking;
use App\Jobs\ProgramApi\ProgramApiSendBookingJob;
use App\Models\Member\Member;
use App\Models\Member\MembershipRenewal;
use App\Models\Payments\Payment;

class BookingApplyMembershipRenewalAction
{
    public function handle(
        MembershipRenewal $membershipRenewal,
    ) {
        $booking = $membershipRenewal->booking;
        $bookingSnapshotData = $booking->getSnapshotData();
        $member = $membershipRenewal->member;
        $startDate = $membershipRenewal->due_date;
        $endDate = $booking->calculateMembershipEndDate($startDate);

        $isPaid = $booking->payment->status === Payment::STATUSES['paid'];
        $membershipStatus = Member::MEMBERSHIP_STATUSES[$isPaid && $startDate->isToday() ? 'active' : 'processing'];

        (new BookingStepFourFillMemberAction())->handle(
            $member,
            $booking,
            $bookingSnapshotData,
            $startDate,
            $endDate,
            $membershipStatus
        );

        $membershipRenewal->markAsCompleted()
            ->save();

        if ($member->program->isProgramSource('gems')) {
            GemsSendBooking::dispatch($booking);
        }

        if ($member->programApiRequest) {
            ProgramApiSendBookingJob::dispatch($booking);
        }
    }
}
