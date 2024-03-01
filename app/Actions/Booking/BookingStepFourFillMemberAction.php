<?php

namespace App\Actions\Booking;

use App\Models\Booking;
use App\Models\Corporate;
use App\Models\Member\Junior;
use App\Models\Member\Kid;
use App\Models\Member\Member;
use App\Models\Member\Partner;
use Carbon\Carbon;

class BookingStepFourFillMemberAction
{
    public function handle(
        Member $member,
        Booking $booking,
        array $bookingData,
        Carbon $startDate,
        Carbon $endDate,
        string $membershipStatus
    ) {
        $this->fillMemberFields($member, $booking, $bookingData['member'], $startDate, $endDate, $membershipStatus);

        $member->membership_source_id ??= $booking->membership_source_id;
        $member->recovery_email = $bookingData['member']['recovery_email'];
        $member->main_email = $bookingData['member']['main_email'];

        if (isset($bookingData['billing']) && !empty($bookingData['billing']['company_name'])
            && $corporate = Corporate::firstOrCreateByTitle($bookingData['billing']['company_name'])) {
            $member->corporate()->associate($corporate);
        };

        $member->save();
        $member->clubs()->sync($booking->clubs->pluck('id'));
        if ($member->plan->membership_duration_id) {
            $member->membershipDurations()->syncWithoutDetaching($member->plan->membership_duration_id);
        }

        $bookingData['member']['id'] = $member->id;
        $bookingData['member']['membership_source_id'] = $member->membership_source_id;

        if (isset($bookingData['billing'], $bookingData['billing']['is_needed']) && $bookingData['billing']['is_needed'] == 1) {
            $this->attachBillingDetails($member, $bookingData['billing']);
        }

        if (isset($bookingData['partner'])) {
            $partnerData = $bookingData['partner'];

            if (empty($partnerData['uuid'])) {
                $partner = new Partner();
            } else {
                $partner = $member->partners()
                    ->where([
                        'uuid' => $partnerData['uuid'],
                        'first_name' => $partnerData['first_name'],
                        'last_name' => $partnerData['last_name'],
                    ])
                    ->firstOrNew();
            }

            $this->fillMemberFields($partner, $booking, $partnerData, $startDate, $endDate, $membershipStatus);

            $partner->referral_code = $member->referral_code;

            $partner->parent()
                ->associate($member)
                ->save();
            $partner->clubs()->sync($booking->clubs->pluck('id'));
            $partner->membershipDurations()->sync($member->membershipDurations->pluck('id'));

            $bookingData['partner']['id'] = $partner->id;
        }

        if (isset($bookingData['junior'])) {
            foreach ($bookingData['junior'] as $key => $juniorData) {
                if (empty($juniorData['uuid'])) {
                    $junior = new Junior();
                } else {
                    $junior = $member->juniors()
                        ->where([
                            'uuid' => $juniorData['uuid'],
                            'first_name' => $juniorData['first_name'],
                            'last_name' => $juniorData['last_name'],
                        ])
                        ->firstOrNew();
                }

                $this->fillMemberFields($junior, $booking, $juniorData, $startDate, $endDate, $membershipStatus);

                $junior->referral_code = $member->referral_code;
                $junior->dob = Carbon::parse($juniorData['birthday']);

                $junior->parent()
                    ->associate($member)
                    ->save();

                $junior->clubs()->sync($booking->clubs->pluck('id'));
                $junior->membershipDurations()->sync($member->membershipDurations->pluck('id'));

                $bookingData['junior'][$key]['id'] = $junior->id;
            }
        }

        if (isset($bookingData['kids'])) {
            foreach ($bookingData['kids'] as $key => $kidsData) {
                if (empty($kidsData['uuid'])) {
                    $kid = new Kid();
                } else {
                    $kid = $member->kids()
                        ->where([
                            'uuid' => $kidsData['uuid'],
                            'first_name' => $kidsData['first_name'],
                        ])
                        ->whereDate('dob', $kidsData['birthday'])
                        ->firstOrNew();
                }

                $kid->first_name = $kidsData['first_name'];
                $kid->last_name = $kidsData['last_name'];
                $kid->dob = Carbon::parse($kidsData['birthday']);

                $kid->member()->associate($member);
                $kid->booking()->associate($member);

                $kid->save();

                $bookingData['kids'][$key]['id'] = $kid->id;
            }
        }

        $booking->addSnapshotData($bookingData);

        return $member;
    }

    public function fillMemberFields(
        Member $member,
        Booking $booking,
        array $data,
        Carbon $startDate,
        Carbon $endDate,
        string $membershipStatus
    ): Member {
        $member->first_name = $data['first_name'];
        $member->last_name = $data['last_name'];
        $member->phone = $data['phone'];
        $member->email = $data['email'];
        $member->avatar = $data['photo'] ?? $member->avatar;
        $member->start_date = $startDate;
        $member->end_date = $endDate;
        $member->membership_status = $membershipStatus;
        $member->dob = $data['birthday'] ?? null;

        $member->area_id = $booking->area_id;
        $member->membershipType()->associate($booking->plan->membershipType);
        $member->plan()->associate($booking->plan);
        $member->booking()->associate($booking);

        return $member;
    }

    public function attachBillingDetails(Member $member, ?array $data)
    {
        $memberBillingDetails = $member->memberBillingDetail()->firstOrNew();
        $memberBillingDetails->first_name = $data['first_name'];
        $memberBillingDetails->last_name = $data['last_name'];
        $memberBillingDetails->country_id = $data['country'];
        $memberBillingDetails->city = $data['city'];
        $memberBillingDetails->state = $data['state'];
        $memberBillingDetails->street = $data['street'];
        $memberBillingDetails->is_gift = isset($data['is_gift']) && !!$data['is_gift'];

        $memberBillingDetails->corporate()->associate($member->corporate);
        $memberBillingDetails->member()->associate($member);
        $memberBillingDetails->save();
    }
}
