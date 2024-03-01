<?php

namespace App\Actions\Booking;

use App\Actions\GiftCard\GiftCardGetDiscountAction;
use App\Exceptions\CouponUnusableException;
use App\Exceptions\GifCard\GiftCardException;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Payments\Payment;

class BookingPriceCalculateAction
{
    public function handle(Booking $booking): Booking
    {
        $plan = $booking->plan;
        $couponDiscountAmount = 0;
        $giftCardDiscountAmount = 0;

        $extraNumberChildren = $booking->number_of_children >= $plan->number_of_free_children ? $booking->number_of_children - $plan->number_of_free_children : 0;
        $extraChildrenAmount = $extraNumberChildren * $plan->extra_child_price;
        $extraChildProgramCommissionAmount = booking_amount_round(
            $extraChildrenAmount * $plan->extra_child_third_party_commission_percent / 100
        );

        $extraNumberOfJuniors = $booking->number_of_juniors >= $plan->number_of_free_juniors ? $booking->number_of_juniors - $plan->number_of_free_juniors : 0;
        $extraJuniorsAmount = $extraNumberOfJuniors * $plan->extra_junior_price;
        $extraJuniorProgramCommissionAmount = booking_amount_round(
            $extraJuniorsAmount * $plan->extra_junior_third_party_commission_percent / 100
        );

        if ($booking->gift_card_amount && $booking->giftCard && $booking->gift_card_number) {
            try {
                $giftCardDiscountAmount = (new GiftCardGetDiscountAction())
                    ->handle($booking->giftCard, $booking->gift_card_number, $booking->gift_card_amount);
            } catch (GiftCardException $exception) {
                //
            }
        }

        $planAmount = $plan->price_without_vat;
        $planProgramCommissionAmount = $planAmount * $plan->price_third_party_commission_percent / 100;
        $subtotalAmount = $planAmount + $extraChildrenAmount + $extraJuniorsAmount;

        if ($booking->coupon) {
            try {
                Coupon::checkUsable($booking->coupon, $booking->email, $plan->id);
                $couponDiscountAmount = $booking->coupon->calculateDiscount($subtotalAmount);
            } catch (CouponUnusableException $exception) {
                //
            }

            // For case when coupon is fixed amount, we need to get discount percent
            $discountPercent = $booking->coupon->getDiscountPercent($subtotalAmount);

            $planProgramCommissionAmount = $planProgramCommissionAmount * (1 - $discountPercent / 100);
            $extraChildProgramCommissionAmount = $extraChildProgramCommissionAmount * (1 - $discountPercent / 100);
            $extraJuniorProgramCommissionAmount = $extraJuniorProgramCommissionAmount * (1 - $discountPercent / 100);
        }

        $discount = $couponDiscountAmount + $giftCardDiscountAmount;
        $subtotalAmountWithDiscount = max($subtotalAmount - $discount, 0);

        $vatAmount = booking_amount_round($subtotalAmountWithDiscount * Payment::VAT);
        $totalPrice = booking_amount_round($subtotalAmountWithDiscount + $vatAmount);

        $booking->total_price = $totalPrice;
        $booking->plan_amount = $planAmount;
        $booking->plan_third_party_commission_amount = booking_amount_round($planProgramCommissionAmount);
        $booking->extra_child_amount = $extraChildrenAmount;
        $booking->extra_child_third_party_commission_amount = booking_amount_round($extraChildProgramCommissionAmount);
        $booking->extra_junior_amount = $extraJuniorsAmount;
        $booking->extra_junior_third_party_commission_amount = booking_amount_round(
            $extraJuniorProgramCommissionAmount
        );
        $booking->subtotal_amount = $subtotalAmount;
        $booking->vat_amount = $vatAmount;
        $booking->coupon_amount = $couponDiscountAmount;
        $booking->gift_card_discount_amount = $giftCardDiscountAmount;

        return $booking;
    }
}
