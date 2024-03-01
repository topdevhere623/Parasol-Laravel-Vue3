<?php

namespace App\Actions;

use App\Exceptions\Payments\MakePaymentException;
use App\Jobs\Zoho\CreatePaymentJob;
use App\Mail\MonthlyPayments\MonthlyPaymentMembershipActivatedMail;
use App\Mail\MonthlyPayments\MonthlyPaymentsInvoiceMail;
use App\Models\Member\Member;
use App\Models\Member\MemberPaymentSchedule;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentMethod;
use App\Models\Payments\PaymentType;
use App\Services\Payment\Models\Customer;
use App\Services\Payment\Models\Product;
use App\Services\Payment\PaymentMethods\CheckoutPaymentMethod;
use Checkout\Models\Payments\IdSource;
use Checkout\Models\Payments\Method;

// Action class charge member for membership (schedule payment)

class ProcessMemberSchedulePaymentAction
{
    protected CheckoutPaymentMethod $checkoutPaymentMethod;

    public function __construct(CheckoutPaymentMethod $checkoutPaymentMethod)
    {
        $this->checkoutPaymentMethod = $checkoutPaymentMethod;
    }

    /**
     * @throws \Throwable
     * @throws \App\Exceptions\Payments\MakePaymentException
     */
    public function handle(MemberPaymentSchedule $memberPaymentSchedule, ?Method $paymentMethod = null): object
    {
        $memberPaymentSchedule = $memberPaymentSchedule->load('member.plan', 'booking');

        $booking = $memberPaymentSchedule->booking;
        $member = $memberPaymentSchedule->member;

        $paymentMonth = now()->format('nY');
        $paymentType = PaymentType::find(PaymentType::NAME_ID['recurring']);

        $payment = $memberPaymentSchedule
            ->payments()
            ->wherePivot('payment_month', $paymentMonth)
            ->first();

        if (!$payment) {
            $payment = new Payment();

            // Subtotal amount without VAT, VAT will be calculated by payment model
            $payment->subtotal_amount = booking_amount_round(
                $memberPaymentSchedule->calculateChargeAmount() / (1 + Payment::VAT)
            );

            $payment->reference_id = $booking->reference_id;
            $payment->offer_code = optional($booking->coupon)->code;
            $payment->third_party_commission_amount = $memberPaymentSchedule->third_party_commission_amount;
            $payment->member()->associate($member);
            $payment->paymentMethod()->associate(PaymentMethod::CHECKOUT_MONTHLY_PAYMENT_ID);
            $payment->paymentType()->associate($paymentType);
            $payment->zohoInvoice()->associate($booking->zohoInvoice);

            $payment->save();
            $memberPaymentSchedule->payments()->attach(
                $payment,
                [
                    'payment_month' => $paymentMonth,
                ]
            );
        }

        $payment->is_recurring = !$paymentMethod;
        $payment->save();

        $customer = new Customer($member->first_name, $member->last_name, $member->email);
        $product = new Product(
            title: $booking->plan->title,
            reference_id: $payment->reference_id,
            product_id: $member->plan->id,
            description: $paymentType->title,
            price: $memberPaymentSchedule->calculateChargeAmount()
        );

        try {
            $makePaymentResult = $this->checkoutPaymentMethod->makePayment(
                $payment,
                $customer,
                $product,
                $paymentMethod ?? new IdSource($memberPaymentSchedule->card_token)
            );
            $payment->markAsPaid()
                ->save();

            CreatePaymentJob::dispatch($payment);

            $memberPaymentSchedule->card_change_auth_token = null;
            $memberPaymentSchedule->markAsActive();
            $memberPaymentSchedule->charge_date = $memberPaymentSchedule->charge_date->addMonths(
                $memberPaymentSchedule->calculateOverdueMonths()
            );
            $memberPaymentSchedule->save();

            \Mail::to($member->email)
                ->send(new MonthlyPaymentsInvoiceMail($payment));

            if ($member->membership_status == Member::MEMBERSHIP_STATUSES['payment_defaulted_on_hold']) {
                $member->membership_status = $member->end_date->isFuture(
                ) ? Member::MEMBERSHIP_STATUSES['active'] : Member::MEMBERSHIP_STATUSES['expired'];
                $member->save();

                \Mail::to($member->email)
                    ->send(new MonthlyPaymentMembershipActivatedMail($memberPaymentSchedule));
            }

            return $makePaymentResult;
        } catch (MakePaymentException $exception) {
            $payment->description = $exception->getMessage();
            $payment->markAsFailed()
                ->save();

            $memberPaymentSchedule->card_status = MemberPaymentSchedule::CARD_STATUS['failed'];
            $memberPaymentSchedule->save();

            throw $exception;
        }
    }
}
