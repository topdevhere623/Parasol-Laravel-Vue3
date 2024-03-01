<?php

namespace App\Http\Controllers\Web\Booking\Payment;

use App\Actions\Booking\BookingPriceCalculateAction;
use App\Http\Controllers\Web\Booking\BookingBaseController;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Package;
use Illuminate\Http\Request;

class BookingCalculateController extends BookingBaseController
{
    public function calculate(
        Request $request,
        BookingPriceCalculateAction $bookingPriceCalculateAction
    ) {
        $booking = new Booking();
        $booking->email = $request->email;
        $booking->number_of_children = (int)$request->input('number_of_children', 0);
        $booking->number_of_juniors = (int)$request->input('number_of_juniors', 0);

        $package = Package::findOrFail($request->package_id);
        $plan = $package->activePlans()->findOrFail($request->plan_id);

        if ($package->apply_coupon) {
            $coupon = Coupon::whereCode($package->apply_coupon)->first();
        } else {
            $coupon = $request->coupon_code ? Coupon::active()->where('code', $request->coupon_code)->first() : null;
        }

        if ($request->gift_card_number && $request->gift_card_amount && $package->activeGiftCard) {
            $booking->gift_card_number = $request->gift_card_number;
            $booking->gift_card_amount = $request->gift_card_amount;
            $booking->giftCard()->associate($package->activeGiftCard);
        }

        $booking->plan()->associate($plan);
        $booking->coupon()->associate($coupon);

        $bookingPriceCalculateAction->handle($booking);

        $data = $booking->only([
            'plan_amount',
            'total_price',
            'extra_child_amount',
            'extra_junior_amount',
            'subtotal_amount',
            'vat_amount',
            'coupon_amount',
            'gift_card_discount_amount',
        ]);

        return response()->json(['data' => $data]);
    }
}
