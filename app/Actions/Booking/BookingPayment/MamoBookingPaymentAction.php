<?php

namespace App\Actions\Booking\BookingPayment;

use App\Services\Payment\PaymentMethods\MamoPaymentMethod;

class MamoBookingPaymentAction extends BaseBookingPaymentAction
{
    public function handle(array $params): ?string
    {
        $params += ['booking' => $this->booking];

        $paymentResult = \App::make(MamoPaymentMethod::class)
            ->getPaymentLink($this->payment, $this->customer, $this->product, $params);

        return $paymentResult->url ?? null;
    }
}
