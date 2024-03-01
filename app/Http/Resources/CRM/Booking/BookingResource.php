<?php

namespace App\Http\Resources\CRM\Booking;

use App\Enum\Booking\StepEnum;
use App\Models\Country;
use App\Models\Member\Member;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Booking */
class BookingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'reference_id' => $this->reference_id,
            'created_at' => $this->created_at->format(config('app.DATE_FORMAT')),
            'membership_source' => optional($this->membershipSource)->title,
            'step' => $this->step,
            'number_of_children' => $this->number_of_children,
            'number_of_juniors' => $this->number_of_juniors,
            'subtotal_amount' => $this->moneyWhenField($this->subtotal_amount),
            'plan_amount' => $this->moneyWhenField($this->plan_amount),
            'extra_child_amount' => $this->moneyWhenField($this->extra_child_amount),
            'extra_junior_amount' => $this->moneyWhenField($this->extra_junior_amount),
            'coupon_amount' => $this->moneyWhenField($this->coupon_amount),
            'coupon' => $this->coupon()->first(['id', 'code']),
            'gift_card_discount_amount' => $this->moneyWhenField($this->gift_card_discount_amount),
            'vat_amount' => $this->moneyWhenField($this->vat_amount),
            'total_price' => $this->moneyWhenField($this->total_price),
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_renewal' => $this->is_renewal,
            'plan' => BookingPlanResource::make($this->plan),
            'clubs' => BookingClubResource::collection($this->clubs),
            'continue_url' => $this->step != StepEnum::Completed ? route('booking.step-'.$this->step->getOldValue(), $this->resource) : null,
            'sales_person' => $this->lead?->backofficeUser?->full_name,
            'payment_transactions' => $this->when(
                !!$this->payment,
                fn () => BookingPaymentTransactionResource::collection(
                    $this->payment->paymentTransactions()
                        ->with('paymentMethod')
                        ->get()
                )
            ),
            'snapshot_data' => $this->snapshotData(),
        ];
    }

    public function moneyWhenField($field)
    {
        return $this->when($field, money_formatter($field));
    }

    public function snapshotData()
    {
        $data = $this->getSnapshotData();
        $memberId = !empty($data['member']['id']) ? $data['member']['id'] : $this->member_id;

        if ($memberId) {
            $member = Member::find($memberId);
            if ($member) {
                $data['member']['id'] = $memberId;
                $data['member']['avatar'] = file_url($member, 'avatar', 'medium');
                $data['member']['member_id'] = $member->member_id;
            }
        }

        if (!empty($data['partner'])) {
            $partner = null;
            if (!empty($data['partner']['id'])) {
                $partner = Member::find($data['partner']['id']);
            } elseif (!empty($data['partner']['uuid'])) {
                $partner = Member::where('uuid', $data['partner']['uuid'])->first();
            }
            if ($partner) {
                $data['partner']['avatar'] = file_url($partner, 'avatar', 'medium');
                $data['partner']['member_id'] = $partner->member_id;
            }
        }

        if (!empty($data['junior']) && is_array($data['junior'])) {
            foreach ($data['junior'] as $key => $juniorData) {
                $junior = null;
                if (!empty($juniorData['id'])) {
                    $junior = Member::find($juniorData['id']);
                } elseif (!empty($juniorData['uuid'])) {
                    $junior = Member::where('uuid', $juniorData['uuid'])->first();
                }

                if ($junior) {
                    $data['junior'][$key]['avatar'] = file_url($junior, 'avatar', 'medium');
                    $data['junior'][$key]['member_id'] = $junior->member_id;
                }
            }
        }
        if (!empty($data['billing']) && !empty($data['billing']['country']) && is_int($data['billing']['country'])) {
            $data['billing']['country'] = Country::find($data['billing']['country'])->country_name;
        }
        return $data;
    }
}
