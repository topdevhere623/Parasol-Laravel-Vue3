<?php

namespace App\Http\Controllers\Web\Booking\Payment;

use App\Exceptions\Payments\MakePaymentException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\Payment\Models\Product;
use App\Services\Payment\PaymentMethods\AmazonPayfortPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BookingPaymentAmazonController extends Controller
{
    // TODO: refactor separate payment services hooks to different controllers
    public function request(
        Booking $booking,
        AmazonPayfortPaymentMethod $amazonPayfortPaymentMethod
    ): View {
        $tokenizationData = [
            'return_url' => route('booking.payment.amazon-response', $booking),
        ];

        $product = new Product(
            title: $booking->plan->title,
            reference_id: "{$booking->reference_id}-".\Str::random(5),
            product_id: $booking->plan->id,
            description: '',
            price: $booking->total_price,
        );

        $formUrl = $amazonPayfortPaymentMethod->getTokenizationApiUrl();
        $formData = $amazonPayfortPaymentMethod->tokenizationRequest($product, $tokenizationData);

        return view(
            'layouts.booking.payment.amazon-pay-fort.tokenization-request',
            compact('formData', 'formUrl')
        );
    }

    public function response(
        Request $request,
        Booking $booking,
        AmazonPayfortPaymentMethod $amazonPayfortPaymentMethod
    ): View {
        // Check for encode signature (security issues)
        $amazonResponseSignature = $amazonPayfortPaymentMethod->calculateSignature(
            $request->except('signature'),
            AmazonPayfortPaymentMethod::SIGNATURE_ENCODE_RESPONSE
        );

        abort_if($amazonResponseSignature !== $request->input('signature'), Response::HTTP_BAD_REQUEST);

        $responseData = [
            'status' => false,
        ];

        if ($request->input('status') == '00') {
            report(
                new MakePaymentException(
                    MakePaymentException::DEFAULT_CARD_ERROR,
                    new \Exception('Amazon response fail: '.json_encode($request->toArray()))
                )
            );
            $responseData['token'] = base64_encode(json_encode($request->toArray()));
            $responseData['url'] = route('booking.payment.fail', $booking);
            $responseData['error_message'] = view('layouts.booking.payment.modals.error-default')->render();
        } else {
            $responseData['token'] = base64_encode(json_encode($request->toArray()));
            $responseData['status'] = true;
        }

        return view(
            'layouts.booking.payment.amazon-pay-fort.tokenization-response',
            compact('responseData')
        );
    }

    public function webHook(Request $request): void
    {
        info(json_encode($request->toArray()));
    }
}
