<?php

namespace App\Actions\Booking\BookingPayment;

use App\Services\Payment\PaymentMethods\TabbyPaymentMethod;

class TabbyThreeBookingPaymentAction extends BaseBookingPaymentAction
{
    protected int $paymentsCount = 3;

    protected string $merchantId = 'advplus';

    public function handle(array $params): ?string
    {
        $params += ['booking' => $this->booking];

        $paymentResult = \App::make(TabbyPaymentMethod::class)
            ->setMerchantId($this->merchantId)
            ->makePayment($this->payment, $this->customer, $this->product, $params);

        return $paymentResult->url[$this->paymentsCount] ?? null;
    }
}
