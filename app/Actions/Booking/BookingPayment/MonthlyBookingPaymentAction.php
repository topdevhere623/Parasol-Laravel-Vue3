<?php

namespace App\Actions\Booking\BookingPayment;

use App\Actions\Booking\BookingCompletePaymentAction;
use App\Exceptions\Payments\MakePaymentException;
use App\Models\Member\MemberPaymentSchedule;
use App\Services\Payment\PaymentMethods\CheckoutPaymentMethod;
use Checkout\Models\Payments\TokenSource;

class MonthlyBookingPaymentAction extends BaseBookingPaymentAction
{
    public function handle(array $params): ?string
    {
        $booking = $this->booking;
        $payment = $this->payment;

        $starDate = $booking->membershipRenewal?->calculateDueDate();
        $monthlyPayment
            = MemberPaymentSchedule::calculate(
                $booking->total_price,
                $booking->plan->getDurationInMonths(),
                $starDate
            );

        $monthlyPaymentWithoutVat
            = MemberPaymentSchedule::calculate(
                $booking->subtotal_amount,
                $booking->plan->getDurationInMonths(),
                $starDate
            );

        $originalTotalAmount = $payment->total_amount;
        $originalDiscount = $payment->discount_amount;

        // Calculate monthly discount amount
        if ($payment->discount_amount) {
            $discountMonthlyPayment = MemberPaymentSchedule::calculate(
                $payment->discount_amount,
                $booking->plan->getDurationInMonths(),
                $starDate
            );

            $payment->discount_amount = $discountMonthlyPayment->first_charge;
            $payment->subtotal_amount = $monthlyPaymentWithoutVat->first_charge + $discountMonthlyPayment->first_charge;
        } else {
            $payment->subtotal_amount = $monthlyPaymentWithoutVat->first_charge;
        }

        // Calculate  amount
        if ($booking->total_third_party_commission_amount) {
            $programCommissionMonthlyPayment = MemberPaymentSchedule::calculate(
                $booking->total_third_party_commission_amount,
                $booking->plan->getDurationInMonths(),
                $starDate
            );

            $payment->third_party_commission_amount = $programCommissionMonthlyPayment->first_charge;
            $programCommissionAmount = $programCommissionMonthlyPayment->monthly_charge;
        } else {
            $programCommissionAmount = 0;
        }

        $this->payment->subtotal_amount = $monthlyPaymentWithoutVat->first_charge;
        $this->payment->save();

        throw_if(
            empty($params['payment_data']['token']),
            new MakePaymentException(MakePaymentException::CARD_TOKEN_IS_REQUIRED)
        );

        try {
            $paymentResult = \App::make(CheckoutPaymentMethod::class)
                ->makePayment(
                    $payment,
                    $this->customer,
                    $this->product,
                    new TokenSource($params['payment_data']['token'])
                );

            $paymentSource = $paymentResult->paymentResponse->source;

            $memberPaymentSchedule = new MemberPaymentSchedule();
            $memberPaymentSchedule->booking()->associate($booking);
            $memberPaymentSchedule->plan()->associate($booking->plan_id);
            $memberPaymentSchedule->charge_date = $monthlyPayment->next_charge_date;
            $memberPaymentSchedule->first_number_of_days = $monthlyPayment->days;
            $memberPaymentSchedule->monthly_amount = $monthlyPayment->monthly_charge;
            $memberPaymentSchedule->monthly_discount_amount = $payment->discount_amount;
            $memberPaymentSchedule->third_party_commission_amount = $programCommissionAmount;
            $memberPaymentSchedule->coupon_code = $booking->coupon?->code;
            $memberPaymentSchedule->first_payment_amount = $monthlyPayment->first_charge;
            $memberPaymentSchedule->card_token = $paymentSource['id'];
            $memberPaymentSchedule->card_last4_digits = $paymentSource['last4'];
            $memberPaymentSchedule->card_scheme = $paymentSource['scheme'];
            $memberPaymentSchedule->setCardExpiryDate($paymentSource['expiry_month'], $paymentSource['expiry_year']);
            $memberPaymentSchedule->save();

            $memberPaymentSchedule->payments()->attach($payment);

            $payment->markAsPaid()
                ->save();

            (new BookingCompletePaymentAction())->handle($booking);

            return route('booking.payment.success', $booking);
        } catch (\Exception $exception) {
            $payment->discount_amount = $originalDiscount;
            $payment->total_amount = $originalTotalAmount;
            $payment->save();

            throw $exception;
        }
    }
}
