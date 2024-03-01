<?php

namespace App\Http\Controllers\Web\Booking\Payment;

use App\Actions\Booking\TabbyResolveBookingPaymentAction;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payments\PaymentTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingPaymentTabbyController extends Controller
{
    public function index(
        Request $request,
        Booking $booking,
        PaymentTransaction $paymentTransaction
    ): RedirectResponse {
        $request->validate([
            'status' => 'nullable',
            'payment_id' => 'string',
        ]);

        if ($paymentTransaction->remote_id != $request->payment_id) {
            report(new \Exception("payments id's doesn't equal"));
            abort(404);
        }

        if (strtolower($request->status) == 'cancel') {
            $paymentTransaction->description = 'Cancelled by customer';
            $paymentTransaction
                ->markAsCancel()
                ->save();

            return redirect()->route('booking.step-2', $booking);
        }

        if ((new TabbyResolveBookingPaymentAction())->handle($paymentTransaction)) {
            return redirect()->route('booking.payment.success', $booking);
        }

        return redirect()->route('booking.payment.fail', $booking);
    }
}
