<?php

namespace App\Actions\Booking;

use App\Models\Booking;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentMethod;
use App\Models\Payments\PaymentType;
use App\Services\Payment\Models\Customer;
use App\Services\Payment\Models\Product;
use App\Services\Payment\PaymentMethods\TabbyPaymentMethod;

class TabbyGetUnavailablePaymentsAction
{
    public function handle(Booking $booking, array $ids): array
    {
        return \Cache::remember(
            'booking-tabby-payment-'.$booking->id,
            120,
            fn () => $this->getAvailablePayments($booking, $ids)
        );
    }

    private function getAvailablePayments(Booking $booking, array $ids): array
    {
        // TODO: find better way
        \DB::beginTransaction();

        $payment = new Payment();

        $payment->subtotal_amount = $booking->subtotal_amount;
        $payment->discount_amount = $booking->coupon_amount + $booking->gift_card_discount_amount;
        $payment->reference_id = $booking->reference_id;
        $payment->offer_code = $booking->coupon?->code;
        $payment->paymentMethod()->associate(PaymentMethod::TABBY_THREE_PAYMENT_ID);
        $payment->paymentType()->associate(
            $booking->plan->paymentType ? $booking->plan->paymentType->id : PaymentType::NAME_ID['membership']
        );
        $payment->save();

        $customer = new Customer($booking->name, '', $booking->email, $booking->phone);
        $product = new Product(
            title: $booking->plan->title,
            reference_id: $booking->reference_id,
            product_id: $booking->plan->id,
            description: $payment->paymentType->title,
            price: $payment->total_amount,
            discount: $payment->discount_amount,
            vat: $payment->vat_amount,
        );

        $unavailableIds = [];

        try {
            if (!empty(
                array_intersect(
                    [PaymentMethod::TABBY_THREE_PAYMENT_ID, PaymentMethod::TABBY_SIX_PAYMENT_ID],
                    $ids
                )
            )) {
                $paymentResult = \App::make(TabbyPaymentMethod::class)
                    ->makePayment($payment, $customer, $product, ['booking' => $booking]);
                $urls = $paymentResult?->url ?? [];

                if (empty($urls[3])) {
                    $unavailableIds[] = PaymentMethod::TABBY_THREE_PAYMENT_ID;
                }

                if (empty($urls[6])) {
                    $unavailableIds[] = PaymentMethod::TABBY_SIX_PAYMENT_ID;
                }
            }

            if (!empty(array_intersect([PaymentMethod::TABBY_FOUR_PAYMENT_ID], $ids))) {
                $paymentResult = \App::make(TabbyPaymentMethod::class)
                    ->setMerchantId('advplus4')
                    ->makePayment($payment, $customer, $product, ['booking' => $booking]);
                $urls = $paymentResult?->url ?? [];

                if (empty($urls[4])) {
                    $unavailableIds[] = PaymentMethod::TABBY_FOUR_PAYMENT_ID;
                }
            }
        } catch (\Exception $exception) {
            report($exception);
        }

        \DB::rollBack();

        return $unavailableIds;
    }
}
