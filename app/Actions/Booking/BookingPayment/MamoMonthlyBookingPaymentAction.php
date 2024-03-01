<?php

namespace App\Actions\Booking\BookingPayment;

use App\Models\Member\MemberPaymentSchedule;

class MamoMonthlyBookingPaymentAction extends MamoBookingPaymentAction
{
    public function handle(array $params): ?string
    {
        $booking = $this->booking;
        $product = $this->product;

        $starDate = $booking->membershipRenewal?->calculateDueDate();
        $planDurationInMonths = $booking->plan->getDurationInMonths();

        $monthlyPayment = MemberPaymentSchedule::calculate(
            $booking->total_price,
            $planDurationInMonths,
            $starDate
        );

        $product->setPrice($monthlyPayment->first_charge);

        $params['save_card'] = true;

        return parent::handle($params);
    }
}
