<?php

namespace App\Actions\Booking\BookingPayment;

use App\Models\Payments\PaymentTransaction;
use App\Services\Payment\PaymentMethods\PaytabsPaymentMethod;
use Illuminate\Support\Facades\Cache;

class PaytabsBookingPaymentAction extends BaseBookingPaymentAction
{
    public function handle(array $params): ?string
    {
        return Cache::remember('paytabs_url_booking_'.$this->booking->id, now()->addMinutes(19), function () {
            $paymentResult = \App::make(PaytabsPaymentMethod::class)
                ->makePayment(
                    $this->payment,
                    $this->customer,
                    $this->product,
                    fn (PaymentTransaction $transaction) => route('booking.payment.paytabs-redirect', [
                        'booking' => $this->booking,
                        'paymentTransaction' => $transaction,
                    ])
                );

            return $paymentResult->paymentResponse->redirect_url ?? null;
        });
    }
}
