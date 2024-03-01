<?php

namespace App\Actions\Booking;

use App\Exceptions\Payments\MakePaymentException;
use App\Models\HSBCBin;
use App\Models\HSBCUsedCard;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentTransaction;
use App\Services\Payment\PaymentMethods\PaytabsPaymentMethod;
use Illuminate\Database\Eloquent\Builder;

class PaytabsResolveBookingPaymentAction
{
    protected PaytabsPaymentMethod $paytabsPaymentMethod;

    protected ?Payment $payment;

    public function __construct()
    {
        $this->paytabsPaymentMethod = app(PaytabsPaymentMethod::class);
    }

    public function handle(PaymentTransaction $paymentTransaction): bool
    {
        $remotePayment = $this->paytabsPaymentMethod
            ->getPayment($paymentTransaction->remote_id);

        if (!$remotePayment) {
            report(
                new \Exception(
                    'Paytabs payment not found '.$paymentTransaction->remote_id.' for booking '.$paymentTransaction->payment->booking->id
                )
            );
            return false;
        }

        $paymentResult = $remotePayment->payment_result;

        report_if(
            !in_array($paymentResult->response_status, ['A', 'C', 'D', 'E', 'X', 'V']),
            new \Exception(
                'Paytabs. Unprocessed payment result for: '.$paymentTransaction->remote_id.' for booking '.$paymentTransaction->payment->booking->id
            )
        );
        // https://support.paytabs.com/en/support/solutions/articles/60000711358-what-is-response-code-vs-the-response-status-
        return match ($paymentResult->response_status) {
            'A' => $this->successPayment($paymentTransaction, $remotePayment),
            'C' => $this->cancelPayment($paymentTransaction),
            'D', 'E' => $this->failPayment($paymentTransaction, $paymentResult->response_message),
            'X' => $this->expiryPayment($paymentTransaction),
            'V' => $this->voidPayment($paymentTransaction),
            default => false,
        };
    }

    protected function successPayment(PaymentTransaction $paymentTransaction, object $remotePayment): bool
    {
        $paymentTransaction->markAsSuccess()
            ->save();

        $payment = $paymentTransaction->payment;
        $booking = $paymentTransaction->payment->booking;
        $packageId = $booking->plan->package->id;
        $planId = $booking->plan->id;

        $paymentMethod = $paymentTransaction->paymentMethod;

        if ($paymentMethod->code == 'paytabs_hsbc' && $paymentTransaction->type == PaymentTransaction::TYPES['authorize']) {
            $cardDetails = PaytabsPaymentMethod::parseCardNumberDetails($remotePayment->payment_info);

            $cardBin = HSBCBin::where('bin', $cardDetails->bin)
                ->active()
                ->first();

            $cardToken = HSBCUsedCard::generateToken(
                $cardDetails->bin,
                $cardDetails->last_4_digits,
                $cardDetails->card_expiry
            );

            if (!$cardBin) {
                $this->voidAndThrow($cardDetails, MakePaymentException::HSBC_NOT_ALLOWED_CARD, $paymentTransaction);
            }

            if ($packageId == settings('hsbc_free_checkout_package_id') && !$cardBin->free_checkout) {
                $this->voidAndThrow(
                    $cardDetails,
                    MakePaymentException::HSBC_USED_FREE_CARD_FOR_PAID_PLAN,
                    $paymentTransaction
                );
            }

            $usedCard = HSBCUsedCard::where('card_token', $cardToken)
                ->whereStatus(HSBCUsedCard::STATUSES['completed'])
                ->when(
                    $booking->membershipRenewal,
                    fn (Builder $query) => $query->where(
                        'member_id',
                        '!=',
                        $booking->membershipRenewal->member_id
                    )
                )
                ->first();

            if ($usedCard) {
                $this->voidAndThrow(
                    $cardDetails,
                    $cardBin->free_checkout ? MakePaymentException::HSBC_ALREADY_USED_FREE_CARD : MakePaymentException::HSBC_ALREADY_USED_CARD,
                    $paymentTransaction
                );
            }

            $this->paytabsPaymentMethod->capture($paymentTransaction->remote_id, $payment);

            $payment->markAsPaid()->save();

            $hsbcUsedCard = new HSBCUsedCard();
            $hsbcUsedCard->package_id = $packageId;
            $hsbcUsedCard->plan_id = $planId;
            $hsbcUsedCard->total_price = $booking->total_price;
            $hsbcUsedCard->bin = $cardDetails->bin;
            $hsbcUsedCard->card_last4_digits = $cardDetails->last_4_digits;
            $hsbcUsedCard->card_scheme = $cardDetails->card_scheme;
            $hsbcUsedCard->card_expiry_date = $cardDetails->card_expiry;
            $hsbcUsedCard->booking()->associate($booking);
            $hsbcUsedCard->payment()->associate($payment);
            $hsbcUsedCard->save();
        }

        $paymentTransaction->payment->paymentMethod()->associate($paymentTransaction->paymentMethod);
        $paymentTransaction->payment->markAsPaid()
            ->save();

        (new BookingCompletePaymentAction())->handle($paymentTransaction->payment->booking);

        return true;
    }

    protected function failPayment(PaymentTransaction $paymentTransaction, ?string $message = null): bool
    {
        $paymentTransaction->description = $message;
        $paymentTransaction->markAsFail()
            ->save();

        if ($paymentTransaction->payment->isPending()) {
            $paymentTransaction->payment->markAsFailed()
                ->save();
        }

        return false;
    }

    protected function cancelPayment(PaymentTransaction $paymentTransaction): bool
    {
        $paymentTransaction->markAsCancel()
            ->save();

        return false;
    }

    protected function voidPayment(PaymentTransaction $paymentTransaction): bool
    {
        if ($paymentTransaction->type == PaymentTransaction::TYPES['void']) {
            $paymentTransaction->markAsSuccess()
                ->save();
            return true;
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

    protected function voidAndThrow($cardDetails, $code, PaymentTransaction $transaction)
    {
        $voidResult = $this->voidPayment2($transaction);
        $voidResult->transaction->description = MakePaymentException::getMessageByCode($code)
            .PHP_EOL.'BIN: '.$cardDetails->bin
            .PHP_EOL.'Last 4 digits: '.$cardDetails->last_4_digits;

        $voidResult->transaction->save();

        throw new MakePaymentException($code);
    }

    protected function voidPayment2(PaymentTransaction $transaction)
    {
        return $this->paytabsPaymentMethod->void(
            $transaction->remote_id,
            $transaction->payment
        );
    }
}
