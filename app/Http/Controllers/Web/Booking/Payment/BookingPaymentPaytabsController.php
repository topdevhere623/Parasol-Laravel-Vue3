<?php

namespace App\Http\Controllers\Web\Booking\Payment;

use App\Actions\Booking\PaytabsResolveBookingPaymentAction;
use App\Enum\Booking\StepEnum;
use App\Exceptions\Payments\MakePaymentException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payments\PaymentTransaction;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Session;

class BookingPaymentPaytabsController extends Controller
{
    public function redirect(Booking $booking, PaymentTransaction $paymentTransaction): RedirectResponse
    {
        if (app()->isProduction()) {
            sleep(3);
        }

        if ($booking->step == StepEnum::Payment) {
            $booking->refresh();
            $paymentTransaction->refresh();
        }

        Cache::forget('paytabs_url_booking_'.$booking->id);
        Cache::forget('paytabs_hsbc_booking_'.$booking->id);

        if ($booking->step != StepEnum::Payment) {
            return redirect()->route('booking.payment.success', $booking);
        }

        try {
            if ((new PaytabsResolveBookingPaymentAction())->handle($paymentTransaction)) {
                return redirect()->route('booking.payment.success', $booking);
            }
        } catch (MakePaymentException|Exception $exception) {
            report($exception);
            Session::flash('booking_payment_error', $exception->getCode());
            return redirect()->route('booking.step-2', $booking);
        }

        // If payment is canceled by user do not show error message
        if ($paymentTransaction->status != PaymentTransaction::STATUSES['cancel']) {
            Session::flash('booking_payment_error', MakePaymentException::DEFAULT_CARD_ERROR);
        }

        return redirect()->route('booking.step-2', $booking);
    }

    public function processPaytabsRequest(Booking $booking, PaymentTransaction $paymentTransaction): JsonResponse
    {
        (new PaytabsResolveBookingPaymentAction())->handle($paymentTransaction);

        return response()->json([
            'message' => 'ok',
        ]);
    }
}
