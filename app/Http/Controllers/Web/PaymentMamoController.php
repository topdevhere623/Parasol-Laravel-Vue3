<?php

namespace App\Http\Controllers\Web;

use App\Actions\Payment\Mamo\PaymentMamoResolveAttachCard;
use App\Actions\Payment\Mamo\PaymentMamoResolvePayment;
use App\Http\Controllers\Controller;
use App\Models\Member\MemberPaymentSchedule;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentMamoLink;
use App\Models\Payments\PaymentTransaction;
use App\Services\Payment\PaymentMethods\MamoPaymentMethod;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentMamoController extends Controller
{
    public const STATUSES = [
        'captured' => PaymentTransaction::STATUSES['success'],
        'card_verified' => PaymentTransaction::STATUSES['success'],
        'failed' => PaymentTransaction::STATUSES['fail'],
    ];

    public function index(Request $request, string $paymentMamoLinkUuid): RedirectResponse
    {
        try {
            Validator::validate($request->all(), [
                'paymentLinkId' => 'required|string',
                'status' => 'required|string',
            ]);
        } catch (Exception $e) {
            report($e);
            abort(404);
        }

        $status = static::STATUSES[$request->status] ?? null;

        report_if(!$status, new \Exception('Invalid status: ').$request->status);
        abort_if(!$status, 404);

        $paymentMamoLink = PaymentMamoLink::where('uuid', $paymentMamoLinkUuid)
            ->with('payment.paymentMethod')
            ->first();

        report_if(!$paymentMamoLink, new \Exception('PaymentMamoLink not found: ').$paymentMamoLinkUuid);
        abort_if(!$paymentMamoLink, 404);

        $mamoPaymentService = app(MamoPaymentMethod::class);
        try {
            $mamoPaymentResponse = $mamoPaymentService->getPayment(
                $request->input('chargeUID', $request->input('transactionId'))
            );
        } catch (RequestException $exception) {
            report($exception);
            abort(404);
        }

        return match (get_class($paymentMamoLink->payable)) {
            Payment::class => (new PaymentMamoResolvePayment())->handle(
                $paymentMamoLink,
                $mamoPaymentResponse,
                $request->input('chargeUID', $request->input('transactionId')),
                $status
            ),
            MemberPaymentSchedule::class => (new PaymentMamoResolveAttachCard())->handle(
                $paymentMamoLink,
                $mamoPaymentResponse,
                $request->input('chargeUID', $request->input('transactionId')),
                $status
            ),
        };
    }
}
