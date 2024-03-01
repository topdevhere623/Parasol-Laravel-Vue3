<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\Payment\PaymentRefundRequest;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentMethod;
use App\Models\Payments\PaymentTransaction;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function refundAvailable(Payment $payment): JsonResponse
    {
        abort_unless($payment->isRefundable(), Response::HTTP_UNPROCESSABLE_ENTITY, 'Payment cannot be refunded');

        return \Prsl::responseData([
            'refundable_amount' => $payment->getRefundableAmount(),
            'payment_method_id' => $payment->payment_method_id,
            'payment_methods' => PaymentMethod::active()->pluck('title', 'id')
                ->transform(function ($option, $index) {
                    return ['value' => $index, 'text' => $option];
                })->values()
                ->toArray(),
        ]);
    }

    public function refund(PaymentRefundRequest $request, Payment $payment)
    {
        abort_unless(
            $payment->isRefundable($request->amount),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Payment cannot be refunded'
        );

        $paymentMethod = PaymentMethod::active()->findOrFail($request->payment_method_id);

        $paymentTransaction = new PaymentTransaction(['type' => PaymentTransaction::TYPES['refund']]);
        $paymentTransaction->markAsSuccess();
        $paymentTransaction->payment()->associate($payment);
        $paymentTransaction->paymentMethod()->associate($paymentMethod);
        $paymentTransaction->amount = $request->amount;
        $paymentTransaction->save();

        $payment->refund_amount = $payment->refund_amount + $request->amount;
        $payment->save();

        $partialRefund = $payment->status == Payment::STATUSES['partial_refunded'] ? ' partially' : '';
        \Prsl::responseSuccess("Payment has been successfully{$partialRefund} refunded");
    }
}
