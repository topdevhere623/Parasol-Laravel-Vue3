<?php

namespace App\Http\Controllers\Api;

use App\Actions\Booking\TabbyResolveBookingPaymentAction;
use App\Http\Controllers\Controller;
use App\Models\Payments\PaymentTransaction;
use Illuminate\Http\Request;

class TabbyWebhookHandler extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
            'status' => 'required|string',
        ]);
        /** @var PaymentTransaction|null $paymentTransaction */
        $paymentTransaction = PaymentTransaction::with('payment')
            ->where('remote_id', $request->id)
            ->first();

        if (!$paymentTransaction) {
            report(new \Exception('Payment transaction not found. '.json_encode($request->toArray())));
            return;
        }

        if (!$paymentTransaction->isPending()) {
            return;
        }

        (new TabbyResolveBookingPaymentAction())->handle($paymentTransaction);
    }
}
