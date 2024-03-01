<?php

namespace App\Actions\Booking\BookingPayment;

use App\Enum\Booking\StepEnum;
use App\Exceptions\Payments\MakePaymentException;
use App\Models\HSBCBin;
use App\Models\HSBCUsedCard;
use App\Services\Payment\PaymentMethods\CheckoutPaymentMethod;
use Carbon\Carbon;
use Checkout\Models\Payments\TokenSource;
use Illuminate\Database\Eloquent\Builder;

class HsbcCheckoutBookingPaymentAction extends BaseBookingPaymentAction
{
    public function handle(array $params): ?string
    {
        throw_if(
            empty($params['payment_data']['token']),
            new MakePaymentException(MakePaymentException::CARD_TOKEN_IS_REQUIRED)
        );

        $packageId = $this->booking->plan->package->id;
        $planId = $this->booking->plan->id;

        $authResult = \App::make(CheckoutPaymentMethod::class)
            ->makeAuth(
                $this->payment,
                $this->customer,
                $this->product,
                new TokenSource($params['payment_data']['token'])
            );
        $source = $authResult->paymentResponse->source;
        $cardBin = HSBCBin::whereBin($source['bin'])->active()->first();

        if (!$cardBin) {
            $this->voidAndThrow($authResult, MakePaymentException::HSBC_NOT_ALLOWED_CARD);
        }

        if ($packageId == settings('hsbc_free_checkout_package_id') && !$cardBin->free_checkout) {
            $this->voidAndThrow($authResult, MakePaymentException::HSBC_USED_FREE_CARD_FOR_PAID_PLAN);
        }

        $usedCard = HSBCUsedCard::where('card_token', $source['id'])
            ->whereStatus(HSBCUsedCard::STATUSES['completed'])
            ->when(
                $this->booking->membershipRenewal,
                fn (Builder $query) => $query->where('member_id', '!=', $this->booking->membershipRenewal->member_id)
            )
            ->first();

        if ($usedCard) {
            $this->voidAndThrow(
                $authResult,
                $cardBin->free_checkout ? MakePaymentException::HSBC_ALREADY_USED_FREE_CARD : MakePaymentException::HSBC_ALREADY_USED_CARD
            );
        }

        $captureResponse = \App::make(CheckoutPaymentMethod::class)
            ->capture($authResult->paymentResponse->id, $this->payment);
        $source = $captureResponse->paymentResponse->source;

        $this->payment->markAsPaid()->save();

        $hsbcUsedCard = new HSBCUsedCard();
        $hsbcUsedCard->package_id = $packageId;
        $hsbcUsedCard->plan_id = $planId;
        $hsbcUsedCard->total_price = $this->booking->total_price;
        $hsbcUsedCard->card_token = $source['id'];
        $hsbcUsedCard->bin = $source['bin'];
        $hsbcUsedCard->card_last4_digits = $source['last4'];
        $hsbcUsedCard->card_scheme = $source['scheme'] ?? null;
        $hsbcUsedCard->card_expiry_date = Carbon::createFromDate(
            $source['expiry_year'],
            $source['expiry_month']
        )->endOfMonth();

        $hsbcUsedCard->booking()->associate($this->booking);
        $hsbcUsedCard->payment()->associate($this->payment);
        $hsbcUsedCard->save();

        $this->booking->step = StepEnum::MembershipDetails;
        $this->booking->save();

        return route('booking.payment.success', $this->booking);
    }

    protected function voidAndThrow($authResult, $code)
    {
        $source = $authResult->paymentResponse->source;
        $voidResult = $this->voidPayment($authResult);
        $voidResult->transaction->description = MakePaymentException::getMessageByCode($code)
            .PHP_EOL.'BIN: '.$source['bin']
            .PHP_EOL.'Last 4 digits: '.$source['last4'];

        $voidResult->transaction->save();

        throw new MakePaymentException($code);
    }

    protected function voidPayment($authResult)
    {
        return \App::make(CheckoutPaymentMethod::class)
            ->void(
                $authResult->paymentResponse->id,
                $this->payment
            );
    }
}
