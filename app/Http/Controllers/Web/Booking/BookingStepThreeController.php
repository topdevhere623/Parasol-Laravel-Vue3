<?php

namespace App\Http\Controllers\Web\Booking;

use App\Actions\Booking\BookingApplyMembershipRenewalAction;
use App\Actions\Booking\BookingStepFourFillMemberAction;
use App\Enum\Booking\StepEnum;
use App\Http\Requests\Web\Booking\BookingStepThreeRequest;
use App\Jobs\Gems\GemsSendBooking;
use App\Jobs\ProgramApi\ProgramApiSendBookingJob;
use App\Jobs\Zoho\CreateInvoiceJob;
use App\Mail\Booking\BookingMemberInvoiceMail;
use App\Models\Booking;
use App\Models\Country;
use App\Models\Member\Member;
use App\Models\Member\MemberPaymentSchedule;
use App\Models\Member\MemberPrimary;
use App\Models\Member\MembershipProcess;
use App\Models\MemberUsedCoupon;
use App\Models\Payments\PaymentMethod;
use App\Models\Referral;
use App\Models\WebFormRequest;
use App\Services\UploadFile\Facades\UploadFile;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Mail;

class BookingStepThreeController extends BookingBaseController
{
    public function index(Booking $booking): RedirectResponse|View
    {
        $booking->load(
            'plan.package.program',
            'membershipRenewal.member.memberBillingDetail.corporate',
            'programApiRequest'
        );

        $showIsGiftBlock = false;
        $showBackButton = true;

        if ($booking->plan->is_giftable === true) {
            $showIsGiftBlock = true;
        }

        if ($booking->membershipRenewal) {
            $showIsGiftBlock = false;
            $memberBillingDetail = $booking->membershipRenewal->member->memberBillingDetail;

            foreach (
                [
                    'first_name',
                    'last_name',
                    'country_id',
                    'city',
                    'tate',
                    'street',
                ] as $field
            ) {
                $billing[$field] = $memberBillingDetail?->{$field};
            }

            $billing['company_name'] = $memberBillingDetail?->corporate?->title;
        } elseif ($booking->programApiRequest) {
            $billing = (array)$booking->programApiRequest->getRequestMemberData();
        } else {
            $data = $booking->getSnapshotData();
            $billing = $data['billing'] ?? [];
        }

        $member = $booking->membershipRenewal?->member?->load('partner', 'kids', 'juniors');
        $startDate = $booking->getStartDateOption();
        $firstName = str($booking->name)->beforeLast(' ')->trim()->title();
        $lastName = str($booking->name)->afterLast(' ')->trim()->title();
        $lastName = $lastName == $firstName ? null : $lastName;

        if (empty($billing) === true) {
            $billing['first_name'] = $firstName;
            $billing['last_name'] = $lastName;
        }

        $billing['is_needed'] = true;

        $presetData['member'] = [
            'first_name' => null,
            'last_name' => null,
            'email' => null,
            'phone' => null,
            'dob' => null,
        ];

        if ($member) {
            $presetData['member']['first_name'] = $member->first_name;
            $presetData['member']['last_name'] = $member->last_name;
            $presetData['member']['email'] = $member->email;
            $presetData['member']['phone'] = $member->phone;
            $presetData['member']['dob'] = $member->dob;
            if ($partner = $member->activePartner ?? $member->partner()->latest()->first()) {
                $presetData['partner']['first_name'] = $partner->first_name;
                $presetData['partner']['last_name'] = $partner->last_name;
                $presetData['partner']['email'] = $partner->email;
                $presetData['partner']['phone'] = $partner->phone;
                $presetData['partner']['dob'] = $partner->dob;
            }
        } elseif ($booking->programApiRequest) {
            $presetData['member'] = (array)$booking->programApiRequest->getRequestMemberData();
            $presetData['partner'] = (array)$booking->programApiRequest->getRequestPartnerData();
        } elseif ($booking->gemsApi && $gemsRequest = $booking->gemsApi->request) {
            $presetData['member']['first_name'] = $gemsRequest['first_name'] ?? null;
            $presetData['member']['last_name'] = $gemsRequest['last_name'] ?? null;
            $presetData['partner']['email'] = $gemsRequest['partner_email'] ?? null;
            $presetData['partner']['phone'] = $gemsRequest['partner_phone'] ?? null;
        }

        $this->theme->setFromPackage($booking->plan->package);

        $countries = Country::get(['id', 'country_name']);

        return view(
            'layouts.booking.step-3',
            compact(
                'booking',
                'countries',
                'billing',
                'showIsGiftBlock',
                'startDate',
                'presetData',
                'member',
                'firstName',
                'lastName',
                'showBackButton'
            )
        );
    }

    public function store(BookingStepThreeRequest $request, Booking $booking): JsonResponse
    {
        $booking->step = StepEnum::MembershipDetails;
        $booking->save();

        $booking->load(
            'plan.membershipType',
            'coupon',
            'payment',
            'membershipRenewal.member.gemsApi',
        );

        DB::beginTransaction();

        $bookingSnapshotData = array_replace_recursive(
            $booking->getSnapshotData(),
            $this->processRequestData($request)
        );

        $startDate = $booking->getStartDate(Carbon::parse($bookingSnapshotData['member']['start_date'] ?? null));

        $bookingSnapshotData['member']['start_date'] = $startDate->format('Y-m-d');

        $booking->addSnapshotData($bookingSnapshotData);
        $booking->units = 1
            + (!empty($bookingSnapshotData['partner']) ? 1 : 0)
            + count($bookingSnapshotData['junior'] ?? []);

        if (!$booking->membershipRenewal) {
            $endDate = $booking->calculateMembershipEndDate($startDate);
            $membershipStatus = Member::MEMBERSHIP_STATUSES['active'];

            if ($startDate->greaterThan(today()) || !$booking->paymentMethod->send_email_invoice) {
                $membershipStatus = Member::MEMBERSHIP_STATUSES['processing'];
            }

            $member = (new BookingStepFourFillMemberAction())->handle(
                new MemberPrimary(),
                $booking,
                $bookingSnapshotData,
                $startDate,
                $endDate,
                $membershipStatus
            );

            if ($booking->gemsApi) {
                $booking->gemsApi->mem_adv_plus_id = $member->member_id;
                $booking->gemsApi->member()->associate($member);
                $booking->gemsApi->save();
                GemsSendBooking::dispatchAfterResponse($booking);
            }

            if ($booking->programApiRequest) {
                $booking->programApiRequest->member()->associate($member);
                $booking->programApiRequest->save();
                ProgramApiSendBookingJob::dispatchAfterResponse($booking);
            }
        } else {
            $member = $booking->membershipRenewal->member;

            $booking->membershipRenewal->due_date = $startDate;
            $booking->membershipRenewal->newPlan()->associate($booking->plan);
            $booking->membershipRenewal->markAsAwaitingDueDate();

            if ($startDate->isToday() || $member->isExpired()) {
                (new BookingApplyMembershipRenewalAction())->handle($booking->membershipRenewal);
            }
            $booking->membershipRenewal->save();
        }

        $memberPaymentSchedule = MemberPaymentSchedule::where('booking_id', $booking->id)->first();
        $memberPaymentSchedule?->member()
            ->associate($member)
            ->save();

        $this->assignCouponUsage($booking, $member);

        DB::commit();

        $booking->payment->member()->associate($member)->save();

        $booking->step = StepEnum::Completed;
        $booking->member()->associate($member)->save();

        if ($booking->hsbcUsedCard) {
            $booking->hsbcUsedCard->member()->associate($member)->save();
        }

        if ($webFormRequest = WebFormRequest::where([
            'email' => $booking->email,
        ])->first()) {
            $booking->update([
                'web_form_request_id' => $webFormRequest->id,
            ]);
            MembershipProcess::create([
                'web_form_request_id' => $webFormRequest->id,
                'status' => MembershipProcess::STATUSES['pending'],
                'title' => 'Booked after Web Form request',
                'member_id' => $member->id,
                'full_name' => $member->full_name,
                'action_due_date' => today(),
            ]);
        }

        Mail::to($booking->email)
            ->when($booking->paymentMethod->send_email_invoice)
            ->send(new BookingMemberInvoiceMail($booking));

        CreateInvoiceJob::dispatch(
            $booking->payment,
            $booking->paymentMethod->id === PaymentMethod::CHECKOUT_MONTHLY_PAYMENT_ID
        );

        return response()->json(['url' => route('booking.step-4', $booking)]);
    }

    private function processRequestData(BookingStepThreeRequest $request)
    {
        $bookingSnapshotData = $request->validated();

        $bookingSnapshotData['member']['photo'] = $this->uploadPhoto('member.photo');

        if ($request->has('partner')) {
            $bookingSnapshotData['partner']['photo'] = $this->uploadPhoto('partner.photo');
        }
        if ($request->has('junior')) {
            $bookingSnapshotData['junior'] = $request->input('junior');
            foreach ($bookingSnapshotData['junior'] as $key => $juniorData) {
                $bookingSnapshotData['junior'][$key]['photo'] = $this->uploadPhoto('junior.'.$key.'.photo');
            }
        }

        return $bookingSnapshotData;
    }

    private function uploadPhoto(string $inputName): null|string
    {
        return UploadFile::upload(
            request()->file($inputName),
            Member::getFilePath('avatar'),
            Member::getFileSize('avatar'),
            Member::getFileAction('avatar')
        );
    }

    private function assignCouponUsage(Booking $booking, Member $member): void
    {
        if (!$coupon = $booking->coupon) {
            return;
        }
        $couponMember = new MemberUsedCoupon();
        $couponMember->code = $coupon->code;
        $couponMember->member()->associate($member);
        $couponMember->coupon()->associate($coupon);
        $couponMember->save();
        if (!$coupon->member) {
            return;
        }

        $member->offer_code = $coupon->code;
        $member->save();

        $data = [
            'code' => $coupon->code,
            'status' => Referral::STATUSES['joined'],
            'member_no' => $member->member_id,
            'used_member_id' => $member->id,
        ];
        if (
            $referral = Referral::where('email', $member->email)
                ->where('status', Referral::STATUSES['lead'])
                ->first()
        ) {
            $referral->update($data);

            return;
        }
        Referral::create(
            array_merge($data, [
                'name' => $member->full_name,
                'email' => $member->email,
                'mobile' => $member->phone,
                'member_id' => $coupon->couponable_id,
            ])
        );
    }
}
