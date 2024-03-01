<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Actions\MakeCheckoutPaymentMethodCardTokenAction;
use App\Actions\ProcessMemberSchedulePaymentAction;
use App\Exceptions\Payments\MakePaymentException;
use App\Http\Resources\MemberPortal\PaymentResource;
use App\Models\Member\MemberPaymentSchedule;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentType;
use App\Services\Payment\Models\Customer;
use App\Services\Payment\Models\Product;
use App\Services\Payment\PaymentMethods\MamoPaymentMethod;
use Checkout\Models\Payments\TokenSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends MemberPortalBaseController
{
    public function index(Request $request): JsonResource|JsonResponse
    {
        if (!$this->member->memberPaymentSchedule) {
            return response()->json(['message' => 'Subscription not found'], Response::HTTP_NOT_FOUND);
        }

        return PaymentResource::collection(
            $this->member->memberPaymentSchedule->payments()
                ->latest('payment_date')
                ->paginate(config('advplus.default_payments_response_limit'))
        );
    }

    public function getCardChangeLink(): JsonResponse
    {
        $memberPaymentSchedule = MemberPaymentSchedule::where('member_id', $this->member->id)
            ->cardChangeable()
            ->first();

        if (!$memberPaymentSchedule) {
            return response()->json(['message' => 'Subscription not found'], Response::HTTP_NOT_FOUND);
        }

        $member = $memberPaymentSchedule->member;
        $customer = new Customer($member->first_name, $member->last_name, $member->email);
        $booking = $memberPaymentSchedule->booking;
        $member = $memberPaymentSchedule->member;

        // Attach card or attach and charge
        if ($memberPaymentSchedule->calculateChargeAmount() == 0) {
            $paymentType = PaymentType::find(PaymentType::NAME_ID['card_change']);
            $product = new Product(
                title: $paymentType->title.' ('.$member->member_id.')',
                reference_id: $member->member_id.'-'.date('his'),
                product_id: null,
                description: $paymentType->title.' ('.$member->member_id.')',
                price: 0,
                discount: 0,
                vat: 0,
                quantity: 0
            );
        } else {
            $paymentMonth = now()->format('nY');
            $paymentType = PaymentType::find(PaymentType::NAME_ID['recurring']);

            $payment = $memberPaymentSchedule
                ->payments()
                ->wherePivot('payment_month', $paymentMonth)
                ->first();

            if (!$payment) {
                $payment = new Payment();

                // Subtotal amount without VAT, VAT will be calculated by payment model
                $payment->subtotal_amount = booking_amount_round(
                    $memberPaymentSchedule->calculateChargeAmount() / (1 + Payment::VAT)
                );

                $payment->reference_id = $booking->reference_id;
                $payment->offer_code = optional($booking->coupon)->code;
                $payment->third_party_commission_amount = $memberPaymentSchedule->third_party_commission_amount;
                $payment->member()->associate($member);
                $payment->paymentMethod()->associate($memberPaymentSchedule->payment_method_id);
                $payment->paymentType()->associate($paymentType);
                $payment->zohoInvoice()->associate($booking->zohoInvoice);

                $payment->save();
                $memberPaymentSchedule->payments()->attach(
                    $payment,
                    [
                        'payment_month' => $paymentMonth,
                    ]
                );
            }

            $product = new Product(
                title: $booking->plan->title,
                reference_id: $booking->reference_id,
                product_id: $booking->plan->id,
                description: $payment->paymentType->title,
                price: $payment->total_amount,
                discount: $payment->discount_amount,
                vat: $payment->vat_amount,
            );
        }

        $paymentResult = \App::make(MamoPaymentMethod::class)
            ->getPaymentLink($memberPaymentSchedule, $customer, $product, ['save_card' => true]);

        return response()->json([
            'data' => [
                'link' => $paymentResult->url,
            ],
        ]);
    }

    public function attachCard(
        Request $request,
        MakeCheckoutPaymentMethodCardTokenAction $attachAction,
        ProcessMemberSchedulePaymentAction $paymentAction
    ): JsonResponse {
        $checkoutToken = $request->validate([
            'token' => ['string', 'required'],
        ])['token'];

        $memberPaymentSchedule = MemberPaymentSchedule::where('member_id', $this->member->id)
            ->cardChangeable()
            ->first();

        if (!$memberPaymentSchedule) {
            return response()->json(['message' => 'Subscription not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            // Attach card or attach and charge
            if ($memberPaymentSchedule->calculateChargeAmount() == 0) {
                $attachResponse = $attachAction->handle($this->member, $checkoutToken);
                $paymentSource = $attachResponse->source;
            } else {
                $paymentResult = $paymentAction->handle($memberPaymentSchedule, new TokenSource($checkoutToken));
                $paymentSource = $paymentResult->paymentResponse->source;
            }
        } catch (MakePaymentException $makePaymentException) {
            return response()->json(['message' => 'Unable to attach card'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $memberPaymentSchedule->refresh();

        $memberEndDate = $memberPaymentSchedule->member->end_date;
        if ($memberEndDate->isCurrentMonth() || $memberEndDate->isPast()) {
            $memberPaymentSchedule->markAsCompleted();
        } else {
            $memberPaymentSchedule->markAsActive();
        }

        $memberPaymentSchedule->setCardExpiryDate($paymentSource['expiry_month'], $paymentSource['expiry_year']);
        $memberPaymentSchedule->card_last4_digits = $paymentSource['last4'];
        $memberPaymentSchedule->card_scheme = $paymentSource['scheme'];
        $memberPaymentSchedule->card_token = $paymentSource['id'];
        $memberPaymentSchedule->card_status = MemberPaymentSchedule::CARD_STATUS['active'];
        $memberPaymentSchedule->save();

        return response()->json(['message' => 'Payment card has been attached successfully']);
    }
}
