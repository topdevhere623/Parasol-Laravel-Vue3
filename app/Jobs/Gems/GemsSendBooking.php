<?php

namespace App\Jobs\Gems;

use App\Models\Booking;
use App\Services\GemsApiService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GemsSendBooking implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $bookingId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($booking)
    {
        $this->bookingId = is_object($booking) ? $booking->id : $booking;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(GemsApiService $gemsMembersService)
    {
        $booking = Booking::with(
            'membershipRenewal',
            'member.partner',
            'member.membershipType',
            'member.kids',
            'member.juniors',
            'member.memberBillingDetail',
            'member.gemsApi',
        )->findOrFail($this->bookingId);

        throw_unless($booking->member->gemsApi, new Exception('Gems api not found for booking id:'.$booking->id));

        $member = $booking->member;
        $gemsApi = $member->gemsApi;

        $transaction_id = 'TRN'.str_pad($member->id, 6, '0', STR_PAD_LEFT);

        $gemsSendData = [
            'label' => $gemsApi->request['token_id'] ?? null,
            'transaction_id' => $transaction_id,
            'trn_Datetime' => now()->format('d-M-y H:i:s'),
            'loyalty_id' => $gemsApi->loyal_id,
            'gems_plus_id' => $member->member_id,
            'membership_type' => optional($member->membershipType)->card_title,
            'expiry' => $member->end_date->toDateString(),
            'first_name' => $member->first_name,
            'last_name' => $member->last_name,
            'phone' => str_replace(' ', '', $member->phone),
            'photo' => file_url($member, 'avatar', 'original'),
            'email' => $member->email,
            'business_email' => $member->recovery_email,
            'start_date' => $member->start_date->toDateString(),
            'subtotal' => $booking->subtotal_amount,
            'tax' => $booking->vat_amount,
            'total_price' => $booking->total_price,
            'user_type' => $gemsApi->request['user_type'] ?? 'staff',
        ];

        if ($booking->membershipRenewal) {
            $gemsSendData['request_type'] = 'renewal';
            $gemsSendData['expiry']
                = $booking->calculateMembershipEndDate($booking->membershipRenewal->due_date)->toDateString();
            $gemsSendData['start_date'] = $booking->membershipRenewal->due_date->toDateString();
        }

        if ($member->memberBillingDetail) {
            $gemsSendData['billing_details'] = [
                'first_name' => $member->memberBillingDetail->first_name,
                'last_name' => $member->memberBillingDetail->last_name,
                'company_name' => optional($member->corporate)->title ?? '',
                'country' => optional($member->memberBillingDetail->country)->country_name,
                'city' => $member->memberBillingDetail->city,
                'state' => $member->memberBillingDetail->state,
                'street' => $member->memberBillingDetail->street,
            ];
        }

        $gemsSendData['billing_details']['partner'] = [];
        $gemsSendData['billing_details']['juniors'] = [];
        $gemsSendData['billing_details']['kids'] = [];

        if ($member->partner) {
            $gemsSendData['billing_details']['partner'] = [
                [
                    'gems_plus_member_id' => $member->partner->member_id,
                    'first_name' => $member->partner->first_name,
                    'last_name' => $member->partner->last_name,
                    'email' => $member->partner->email,
                    'phone' => str_replace(' ', '', $member->partner->phone),
                    'photo' => file_url($member->partner, 'avatar', 'original'),
                ],
            ];
        }

        if ($member->juniors) {
            foreach ($member->juniors as $junior) {
                $gemsSendData['billing_details']['juniors'][] = [
                    'gems_plus_member_id' => $junior->member_id,
                    'first_name' => $junior->first_name,
                    'last_name' => $junior->last_name,
                    'email' => $junior->email,
                    'phone' => str_replace(' ', '', $junior->phone),
                    'birthday' => optional($junior->dob)->toDateString(),
                    'photo' => file_url($junior, 'avatar', 'original'),
                ];
            }
        }
        if ($member->kids) {
            foreach ($member->kids as $kid) {
                $gemsSendData['billing_details']['kids'][] = [
                    'first_name' => $kid->first_name,
                    'last_name' => $kid->last_name,
                    'birthday' => optional($kid->dob)->toDateString(),
                ];
            }
        }
        info(json_encode($gemsSendData));
        if ($gemsResponse = $gemsMembersService->sendMember($gemsSendData)) {
            info($gemsResponse);
            $gemsApi->response_code = $gemsResponse['data']['code'] ?? null;
            $gemsApi->save();
        }
    }
}
