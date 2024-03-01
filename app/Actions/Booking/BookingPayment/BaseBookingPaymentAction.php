<?php

namespace App\Actions\Booking\BookingPayment;

use App\Models\Booking;
use App\Models\Payments\Payment;
use App\Services\Payment\Models\Customer;
use App\Services\Payment\Models\Product;

abstract class BaseBookingPaymentAction
{
    protected Booking $booking;

    protected Payment $payment;

    protected Customer $customer;

    protected Product $product;

    public function __construct(Booking $booking, Payment $payment)
    {
        $this->customer = new Customer($booking->name, '', $booking->email, $booking->phone);
        $this->product = new Product(
            title: $booking->plan->title,
            reference_id: $booking->reference_id,
            product_id: $booking->plan->id,
            description: $payment->paymentType->title,
            price: $payment->total_amount,
            discount: $payment->discount_amount,
            vat: $payment->vat_amount,
        );
        $this->booking = $booking;
        $this->payment = $payment;
    }

    abstract public function handle(array $params): ?string;
}
