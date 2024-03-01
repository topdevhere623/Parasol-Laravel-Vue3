<?php

namespace App\Actions;

use App\Models\Payments\Payment;
use App\Models\Payments\PaymentType;
use App\Services\Payment\Models\Customer;
use App\Services\Payment\Models\Product;
use App\Services\Payment\PaymentMethods\CheckoutPaymentMethod;
use Checkout\Models\Payments\TokenSource;

class MakeCheckoutPaymentMethodCardTokenAction
{
    /**
     * @throws \App\Exceptions\Payments\MakePaymentException
     */
    public function handle($member, string $checkoutToken)
    {
        $paymentType = PaymentType::find(PaymentType::NAME_ID['card_change']);
        $customer = new Customer($member->first_name, $member->last_name, $member->email);
        $product = new Product(
            title: $paymentType->title,
            reference_id: $member->member_id.'-'.date('hi'),
            product_id: null,
            description: $paymentType->title,
            price: Payment::CARD_CHANGE_AUTH_FEE
        );

        return \App::make(CheckoutPaymentMethod::class)
            ->attachCard($customer, $product, new TokenSource($checkoutToken));
    }
}
