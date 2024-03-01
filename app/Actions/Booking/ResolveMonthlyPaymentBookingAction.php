<?php

namespace App\Actions\Booking;

use App\Models\Booking;
use App\Models\Member\MemberPaymentSchedule;
use App\Models\Payments\Payment;

class ResolveMonthlyPaymentBookingAction
{
    public function handle(Payment $payment, Booking $booking, array $cardDetails): void
    {
        $starDate = $booking->membershipRenewal?->calculateDueDate();

        $planDurationInMonths = $booking->plan->getDurationInMonths();

        $discountMonthlyPayment = MemberPaymentSchedule::calculate(
            $booking->discount_amount,
            $planDurationInMonths,
            $starDate
        );

        $thirdPartyCommissionMonthlyPayment = MemberPaymentSchedule::calculate(
            $booking->total_third_party_commission_amount,
            $planDurationInMonths,
            $starDate
        );

        $monthlyPayment = MemberPaymentSchedule::calculate(
            $booking->total_price,
            $planDurationInMonths,
            $starDate
        );

        $monthlyPaymentWithoutVat = MemberPaymentSchedule::calculate(
            $booking->subtotal_amount,
            $planDurationInMonths,
            $starDate
        );

        $payment->subtotal_amount = $monthlyPaymentWithoutVat->first_charge;
        $payment->discount_amount = $discountMonthlyPayment->first_charge;
        $payment->third_party_commission_amount = $thirdPartyCommissionMonthlyPayment->first_charge;
        $payment->markAsPaid()
            ->save();

        $memberPaymentSchedule = new MemberPaymentSchedule([
            'booking_id' => $booking->id,
            'plan_id' => $booking->plan_id,
            'charge_date' => $monthlyPayment->next_charge_date,
            'first_number_of_days' => $monthlyPayment->days,
            'payment_method_id' => $payment->payment_method_id,
            'monthly_amount' => $monthlyPayment->monthly_charge,
            'monthly_discount_amount' => $discountMonthlyPayment->monthly_charge,
            'third_party_commission_amount' => $thirdPartyCommissionMonthlyPayment->monthly_charge,
            'coupon_code' => $booking->coupon?->code,
            'first_payment_amount' => $monthlyPayment->first_charge,
            'card_token' => $cardDetails['card_token'],
            'card_last4_digits' => $cardDetails['last4'],
            'card_scheme' => $cardDetails['scheme'],
        ]);

        if (isset($cardDetails['expiry_month'], $cardDetails['expiry_year'])) {
            $memberPaymentSchedule->setCardExpiryDate($cardDetails['expiry_month'], $cardDetails['expiry_year']);
        }

        $memberPaymentSchedule->save();

        $memberPaymentSchedule->payments()->attach($payment);
    }
}
