<?php

namespace App\Http\Controllers\Web\Booking;

use App\Actions\Booking\BookingPriceCalculateAction;
use App\Actions\Booking\TabbyGetUnavailablePaymentsAction;
use App\Enum\Booking\StepEnum;
use App\Exceptions\Payments\MakePaymentException;
use App\Models\Booking;
use App\Models\Member\MemberPaymentSchedule;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentMethod;
use App\Models\Payments\PaymentType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BookingStepTwoController extends BookingBaseController
{
    public function index(Booking $booking, BookingPriceCalculateAction $bookingPriceCalculateAction): View
    {
        $booking->load('plan.package.program', 'gemsApi', 'membershipRenewal.member', 'giftCard');

        if (!$paymentMethods = $booking->plan->activePaymentMethods) {
            $paymentMethods = PaymentMethod::active()->get();
        }

        $this->theme->setFromPackage($booking->plan->package);

        $bookingPriceCalculateAction->handle($booking);

        $monthlyPayment = null;

        if ($paymentMethods->whereIn('code', ['paytabs_monthly', 'mamo_monthly', 'monthly'])) {
            $monthlyPayment
                = MemberPaymentSchedule::calculate(
                    $booking->total_price,
                    $booking->plan->getDurationInMonths(),
                    $booking->membershipRenewal?->calculateDueDate()
                );
        }

        $focPaymentId = PaymentMethod::FOC_PAYMENT_ID;

        if ($paymentMethods->whereIn(
            'id',
            [
                PaymentMethod::TABBY_THREE_PAYMENT_ID,
                PaymentMethod::TABBY_SIX_PAYMENT_ID,
                PaymentMethod::TABBY_FOUR_PAYMENT_ID,
            ]
        )->isNotEmpty() && $booking->total_price != 0) {
            $unavailableTabbyIds = (new TabbyGetUnavailablePaymentsAction())->handle(
                $booking,
                $paymentMethods->pluck('id')->toArray()
            );

            $paymentMethods->transform(
                fn (PaymentMethod $paymentMethod) => $paymentMethod->setAttribute(
                    'is_available',
                    !in_array($paymentMethod->id, $unavailableTabbyIds)
                )
            );
        }

        return view(
            'layouts.booking.step-2',
            compact(
                'booking',
                'paymentMethods',
                'monthlyPayment',
                'focPaymentId',
            )
        );
    }

    public function store(
        Request $request,
        Booking $booking,
        BookingPriceCalculateAction $bookingPriceCalculateAction
    ): JsonResponse {
        $request->validate([
            'payment_method_code' => 'required',
        ]);

        Cache::put("booking-payment-process-{$request->payment_method_code}-{$booking->id}", true, 30);

        $booking->load(['plan', 'coupon', 'payment']);
        $bookingPriceCalculateAction->handle($booking);

        if ($booking->total_price > 0) {
            $paymentMethod = $booking->plan->paymentMethods()->findOrFail($request->payment_method_code);
        } else {
            if ($HSBCPaymentMethod = $booking->plan->paymentMethods()->find($request->payment_method_code)) {
                if ($HSBCPaymentMethod->code === PaymentMethod::HSBC_CHECKOUT_CODE) {
                    $paymentMethod = $HSBCPaymentMethod;
                }
            }
            $paymentMethod ??= PaymentMethod::find(PaymentMethod::FOC_PAYMENT_ID);
        }

        if (!$payment = $booking->payment) {
            $payment = new Payment();

            $payment->subtotal_amount = $booking->subtotal_amount;
            $payment->discount_amount = $booking->coupon_amount + $booking->gift_card_discount_amount;
            $payment->reference_id = $booking->reference_id;
            $payment->offer_code = $booking->coupon?->code;
            $payment->third_party_commission_amount = $booking->total_third_party_commission_amount;
            $payment->paymentMethod()->associate($paymentMethod);
            $payment->paymentType()->associate(
                $booking->plan->paymentType ? $booking->plan->paymentType->id : PaymentType::NAME_ID['membership']
            );
            $payment->save();

            $booking->payment()->associate($payment);
            $booking->save();
        }

        $booking->payment
            ->paymentMethod()
            ->associate($paymentMethod)
            ->save();

        try {
            $bookingPaymentAction = app()->make(
                \Str::of($paymentMethod->code)
                    ->camel()
                    ->ucfirst()
                    ->prepend('\App\Actions\Booking\BookingPayment\\')
                    ->append('BookingPaymentAction')
                    ->toString(),
                compact('booking', 'payment')
            );

            $actionParams = $request->toArray();
            $actionParams['payment_data'] = json_decode($actionParams['payment_data'], true);

            $url = $bookingPaymentAction->handle($actionParams);
            if (!$url) {
                report('URL payment not generated');
            }

            $booking->paymentMethod()
                ->associate($paymentMethod)
                ->save();

            return response()->json([
                'data' => [
                    'url' => $url,
                ],
            ]);
        } catch (MakePaymentException $exception) {
            $payment->markAsFailed()->save();
            report($exception);
            return response()
                ->json(
                    [
                        'message' => $exception->getMessage(),
                        'data' => [
                            'code' => $exception->getCode(),
                            'content' => view()->first([
                                'layouts.booking.payment.modals.error-code-'.$exception->getCode(),
                                'layouts.booking.payment.modals.error-default',
                            ])->render(),
                        ],
                    ],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        } catch (\Exception $exception) {
            report($exception);

            $payment->markAsFailed()->save();

            return response()->json(
                [
                    'message' => MakePaymentException::getDefaultErrorMessage(),
                    'data' => [
                        'code' => MakePaymentException::DEFAULT_ERROR,
                        'content' => view('layouts.booking.payment.modals.error-default')->render(),
                    ],
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } finally {
            Cache::forget("booking-payment-process-{$request->payment_method_code}-{$booking->id}".$booking->id);
        }
    }

    public function paymentSuccess(Booking $booking): View
    {
        abort_if($booking->step != StepEnum::MembershipDetails, 404);

        $url = route('booking.step-3', $booking);
        $this->theme->setFromPackage($booking->plan->package);

        return view('layouts.booking.payment.success', compact('url'));
    }

    public function paymentFail(Booking $booking): View
    {
        $this->theme->setFromPackage($booking->plan->package);
        return view('layouts.booking.payment.fail', compact('booking'));
    }
}
