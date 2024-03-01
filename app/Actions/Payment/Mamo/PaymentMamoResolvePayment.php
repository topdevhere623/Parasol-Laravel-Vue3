<?php

namespace App\Actions\Payment\Mamo;

use App\Actions\Booking\BookingCompletePaymentAction;
use App\Actions\Booking\ResolveMonthlyPaymentBookingAction;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentMamoLink;
use App\Models\Payments\PaymentMethod;
use App\Models\Payments\PaymentTransaction;
use Illuminate\Http\RedirectResponse;

class PaymentMamoResolvePayment
{
    public function handle(
        PaymentMamoLink $paymentMamoLink,
        object $mamoPaymentResponse,
        string $remoteId,
        string $transactionStatus
    ): RedirectResponse {
        /** @var Payment $payment */
        $payment = $paymentMamoLink->payable;
        $booking = $payment->booking;
        $responsePaymentMethod = $mamoPaymentResponse->payment_method;
        $paymentMethodCode = $responsePaymentMethod->card_id ? 'mamo_monthly' : 'mamo';
        $paymentMethod = $this->getPaymentMethod($paymentMethodCode);

        $paymentTransaction = $this->createPaymentTransaction(
            $remoteId,
            $transactionStatus,
            $mamoPaymentResponse,
            $payment,
            $paymentMethod
        );

        if ($paymentTransaction->status === PaymentTransaction::STATUSES['success']) {
            $this->handleSuccessfulPayment(
                $paymentMethod,
                $payment,
                $booking,
                $responsePaymentMethod,
                $paymentMamoLink
            );
            return redirect()->route('booking.payment.success', $booking);
        }

        $payment->markAsFailed()->save();
        return redirect()->route('booking.payment.fail', $booking);
    }

    private function getPaymentMethod($paymentMethodCode)
    {
        $paymentMethod = PaymentMethod::where('code', $paymentMethodCode)->first();
        report_if(!$paymentMethod, new \Exception('PaymentMethod not found: '.$paymentMethodCode));
        return $paymentMethod;
    }

    private function createPaymentTransaction(
        $remoteId,
        $transactionStatus,
        $mamoPaymentResponse,
        $payment,
        $paymentMethod
    ): PaymentTransaction {
        $paymentTransaction = PaymentTransaction::make([
            'remote_id' => $remoteId,
            'status' => $transactionStatus,
            'type' => PaymentTransaction::TYPES['capture'],
            'amount' => $mamoPaymentResponse->amount,
            'description' => $mamoPaymentResponse->error_message ? $mamoPaymentResponse->error_code.': '.$mamoPaymentResponse->error_message : null,
        ]);

        $paymentTransaction->payment()->associate($payment);
        $paymentTransaction->paymentMethod()->associate($paymentMethod);
        $paymentTransaction->save();
        $paymentTransaction->attachResponse(json_encode($mamoPaymentResponse));

        return $paymentTransaction;
    }

    private function handleSuccessfulPayment(
        $paymentMethod,
        Payment $payment,
        $booking,
        $responsePaymentMethod,
        $paymentMamoLink
    ): void {
        if ($paymentMethod->code == 'mamo_monthly') {
            $cardDetails = [
                'card_token' => $responsePaymentMethod->card_id,
                'last4' => $responsePaymentMethod->card_last4,
                'scheme' => str_contains(strtolower($responsePaymentMethod->type), 'visa') ? 'visa' : 'mastercard',
            ];
            (new ResolveMonthlyPaymentBookingAction())->handle($payment, $booking, $cardDetails);
        }

        $payment->paymentMethod()->associate($paymentMethod);
        $payment->markAsPaid()
            ->save();

        $paymentMamoLink->is_active = false;
        $paymentMamoLink->save();

        (new BookingCompletePaymentAction())->handle($booking);
    }

}
