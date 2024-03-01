<?php

namespace App\Actions\Booking;

use App\Models\Payments\PaymentTransaction;
use App\Services\Payment\PaymentMethods\TabbyPaymentMethod;
use Carbon\Carbon;

class TabbyResolveBookingPaymentAction
{
    protected TabbyPaymentMethod $tabbyPaymentMethod;

    public function __construct()
    {
        $this->tabbyPaymentMethod = app(TabbyPaymentMethod::class);
    }

    public function handle(PaymentTransaction $paymentTransaction): bool
    {
        $remotePayment = $this->tabbyPaymentMethod
            ->getPayment($paymentTransaction->remote_id);

        $isExpired = Carbon::parse($remotePayment['expires_at'])
            ->setTimezone(config('app.timezone'))
            ->isPast();

        // Status maybe created, but expiry date is past
        if ($isExpired && $remotePayment['status'] == TabbyPaymentMethod::STATUSES['created']) {
            return $this->expiryPayment($paymentTransaction);
        }
        return match ($remotePayment['status']) {
            TabbyPaymentMethod::STATUSES['authorized'] => $this->successPayment($paymentTransaction),
            TabbyPaymentMethod::STATUSES['expired'] => $this->expiryPayment($paymentTransaction),
            TabbyPaymentMethod::STATUSES['rejected'] => $this->failPayment($paymentTransaction),
            default => false
        };
    }

    protected function successPayment(PaymentTransaction $paymentTransaction): bool
    {
        if (!$this->tabbyPaymentMethod->capturePayment($paymentTransaction->remote_id, $paymentTransaction->amount)) {
            return $this->failPayment($paymentTransaction);
        }

        $paymentTransaction->markAsSuccess()
            ->save();

        $paymentTransaction->payment->markAsPaid()
            ->save();

        (new BookingCompletePaymentAction())->handle($paymentTransaction->payment->booking);

        return true;
    }

    protected function failPayment(PaymentTransaction $paymentTransaction): bool
    {
        $paymentTransaction->markAsFail()
            ->save();

        if ($paymentTransaction->payment->isPending()) {
            $paymentTransaction->payment->markAsFailed()
                ->save();
        }

        return false;
    }

    protected function expiryPayment(PaymentTransaction $paymentTransaction): bool
    {
        $paymentTransaction->markAsExpiry()
            ->save();

        if ($paymentTransaction->payment->isPending()) {
            $paymentTransaction->payment->markAsFailed()
                ->save();
        }

        return false;
    }
}
