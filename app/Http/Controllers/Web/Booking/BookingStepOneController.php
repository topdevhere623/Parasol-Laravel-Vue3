<?php

namespace App\Http\Controllers\Web\Booking;

use App\Actions\Booking\BookingPriceCalculateAction;
use App\Enum\Booking\StepEnum;
use App\Http\Requests\Web\Booking\BookingStepOneRequest;
use App\Models\Area;
use App\Models\Booking;
use App\Models\City;
use App\Models\Club\Club;
use App\Models\Coupon;
use App\Models\GemsApi;
use App\Models\Member\MembershipRenewal;
use App\Models\Member\MembershipSource;
use App\Models\Package;
use App\Models\Payments\PaymentMethod;
use App\Models\Plan;
use App\Models\ProgramApiRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingStepOneController extends BookingBaseController
{
    public function index(Request $request): View|RedirectResponse
    {
        $plan_id = $request->plan;
        $gems_uuid = $request->gems_uuid;
        $renewal_token = $request->renewal;
        $package_slug = $request->input('package', 'silver-membership');
        $bookingUserDetails = [
            'name' => null,
            'phone' => null,
            'email' => null,
        ];
        $kids_count = 0;
        $juniors_count = 0;
        $area_id = null;
        $city_id = null;
        $gemsLoyalId = null;
        $package = null;

        if ($gems_uuid && $gemsApi = GemsApi::whereUuid($gems_uuid)->first()) {
            $bookingUserDetails['name'] = $gemsApi->request['first_name'];
            $gemsLoyalId = $gemsApi->loyal_id;
        }

        if ($request->request_id && $programApiRequest = ProgramApiRequest::firstWhere('uuid', $request->request_id)) {
            if ($programApiRequest->request) {
                $programMemberData = $programApiRequest->getRequestMemberData();
                $bookingUserDetails['name'] = "{$programMemberData->first_name} {$programMemberData->last_name}";
                $bookingUserDetails['phone'] = $programMemberData->phone;
                $bookingUserDetails['email'] = $programMemberData->email;
            }
            $request->session()->put('bookingProgramApi', $programApiRequest->uuid);
        }

        if ($renewal_token) {
            $membershipRenewal = MembershipRenewal::with('booking')
                ->pending()
                ->with('member', function ($query) {
                    $query->with('plan', 'area');
                    $query->withCount('kids', 'juniors');
                })
                ->where('token', $renewal_token)
                ->firstOrFail();

            if (in_array($membershipRenewal->booking?->step, StepEnum::afterPaymentSteps())) {
                return redirect()->route(
                    'booking.step-'.$membershipRenewal->booking->step->getOldValue(),
                    $membershipRenewal->booking->uuid
                );
            }

            $plan_id ??= $membershipRenewal->member->plan->id;
            $package = $membershipRenewal->renewalPackage ?? $membershipRenewal->member->plan->renewalPackage;
            $package_slug = $package->slug;
            $bookingUserDetails['name'] = $membershipRenewal->member->first_name;
            $bookingUserDetails['phone'] = $membershipRenewal->member->phone;
            $bookingUserDetails['email'] = $membershipRenewal->member->login_email;
            $kids_count = $membershipRenewal->member->kids_count;
            $juniors_count = $membershipRenewal->member->juniors_count;
            $area_id = $membershipRenewal->member->area?->id;
            $city_id = $membershipRenewal->member->area?->city?->id;

            if (!$package) {
                return redirect('/#join');
            }
        }

        $package = Package::active()->whereSlug($package_slug)->with('activePlans', function ($query) {
            $query->oldest('sort');
            $query->with('fixedVisibleInPlanClubs', function ($query) {
                $query->select('clubs.id');
            });
            $query->withExists([
                'activePaymentMethods as has_tabby_three_payment' => function (Builder $query) {
                    $query->where('payment_methods.id', PaymentMethod::TABBY_THREE_PAYMENT_ID);
                },
            ]);

            $query->withExists([
                'activePaymentMethods as has_tabby_four_payment' => function (Builder $query) {
                    $query->where('payment_methods.id', PaymentMethod::TABBY_FOUR_PAYMENT_ID);
                },
            ]);

            $query->withExists([
                'activePaymentMethods as has_tabby_six_payment' => function (Builder $query) {
                    $query->where('payment_methods.id', PaymentMethod::TABBY_SIX_PAYMENT_ID);
                },
            ]);
        })
            ->with('activeGiftCard')
            ->first();

        if (!$package) {
            return redirect('/#join');
        }

        $this->theme->setFromPackage($package);

        $membershipSources = $renewal_token
            ? null
            : ($package->membershipSource ? [] : MembershipSource::sort()->displayOnBooking()->get());

        $plans = $package->activePlans;
        $plans->each(function (Plan $plan) {
            $plan->setAttribute('booking_clubs_details', $plan->getBookingClubsDetails());

            $plan->tabbyPaymentsCount = match (true) {
                $plan->has_tabby_three_payment => 3,
                $plan->has_tabby_four_payment => 4,
                $plan->has_tabby_six_payment => 6,
                default => 0,
            };
        });

        $hasTabbyPayment = $plans->where('tabbyPaymentsCount', '!=', 0)->isNotEmpty();

        $selectedPlan = $plans->firstWhere('id', $plan_id) ?? $plans->first();
        $clubs = Club::visibleInPlan()
            ->sort()
            ->active()
            ->with('city')
            ->get();

        $clubCities = $clubs->groupBy('city_id')->map(function ($clubs) {
            return $clubs->first()->city->name;
        });

        $coupon = $package->apply_coupon ?: ($package->show_coupons ? $request->coupon : null);
        $cities = City::all();

        return view(
            'layouts.booking.step-1',
            compact(
                'package',
                'clubs',
                'clubCities',
                'coupon',
                'plans',
                'selectedPlan',
                'membershipSources',
                'gems_uuid',
                'bookingUserDetails',
                'renewal_token',
                'kids_count',
                'juniors_count',
                'cities',
                'area_id',
                'city_id',
                'hasTabbyPayment',
                'gemsLoyalId',
            )
        );
    }

    public function store(
        BookingStepOneRequest $request,
        BookingPriceCalculateAction $bookingPriceCalculateAction
    ): JsonResponse {
        $couponCode = $request->coupon_code;
        $numberOfChildren = (int)$request->input('number_of_children', 0);
        $numberOfJuniors = (int)$request->input('number_of_juniors', 0);
        $renewalToken = $request->renewal_token;
        $membershipRenewal = null;

        if ($renewalToken) {
            $membershipRenewal = MembershipRenewal::with('member.plan')
                ->where('token', $renewalToken)
                ->firstOrFail();

            $package = $membershipRenewal->renewalPackage ?? $membershipRenewal->member->plan->renewalPackage;
        } else {
            $package = Package::findOrFail($request->package_id);
        }

        $plan = $package->activePlans()->findOrFail($request->plan_id);

        if ($plan->allowed_club_type == Plan::ALLOWED_CLUB_TYPES['all_available']) {
            $clubs = $plan->getBookingClubsDetails()->available_clubs;
        } else {
            $requestClubsIds = collect(explode(',', trim($request->clubs, ','), $plan->number_of_clubs));
            $clubs = $plan->availableClubs()
                ->whereIn('clubs.id', $requestClubsIds)
                ->pluck('clubs.id');
        }

        // Cove beach club is unavailable in Dubai Blue Waters location
        // TODO: refactor this to general setting
        if ($request->area_id == Area::DUBAI_BLUE_WATER_ID) {
            $clubs = $clubs->diff([Club::COVE_BEACH_ID]);
        }

        if ($package->apply_coupon) {
            $coupon = Coupon::whereCode($package->apply_coupon)->first();
        } else {
            $coupon = $couponCode ? Coupon::active()->whereCode($couponCode)->first() : null;
        }

        $membershipSource = $package->membershipSource ?? MembershipSource::getOrCreateMembershipSource(
            $request->membership_source_id,
            $request->membership_source_other
        );

        $booking = new Booking();
        $booking->name = $membershipRenewal ? $membershipRenewal->member->first_name : $request->name;
        $booking->email = $request->email;
        $booking->phone = $membershipRenewal ? $membershipRenewal->member->phone : $request->phone;
        $booking->number_of_children = $numberOfChildren <= $plan->number_of_allowed_children ? $numberOfChildren : $plan->number_of_allowed_children;
        $booking->number_of_juniors = $numberOfJuniors <= $plan->number_of_allowed_juniors ? $numberOfJuniors : $plan->number_of_allowed_juniors;
        $booking->area_id = $request->area_id;
        $booking->type = Booking::TYPES[$membershipRenewal ? 'renewal' : 'booking'];
        $booking->membershipSource()->associate($membershipSource);

        $booking->coupon()->associate($coupon);
        $booking->plan()->associate($plan);

        if ($request->gift_card_number && $request->gift_card_amount && $package->activeGiftCard) {
            $booking->gift_card_number = $request->gift_card_number;
            $booking->gift_card_amount = $request->gift_card_amount;
            $booking->giftCard()->associate($package->activeGiftCard);
        }

        $bookingPriceCalculateAction->handle($booking);

        $booking->step = StepEnum::Payment;

        $booking->member()->associate($membershipRenewal?->member);
        $booking->save();
        $booking->clubs()->attach($clubs);

        if ($membershipRenewal) {
            $membershipRenewal->booking()->associate($booking);
            $membershipRenewal->save();
        }

        $booking->addSnapshotData(['clubs' => $booking->clubs->pluck('title', 'id')]);

        if ($gemsUuid = $request->input('gems_uuid')) {
            $gemsApi = GemsApi::whereUuid($gemsUuid)
                ->first();
            if ($gemsApi) {
                $gemsApi->booking()
                    ->associate($booking)
                    ->save();
            } else {
                report('Gems API by uuid not found. uuid: '.$gemsUuid);
            }
        }

        if ($bookingProgramApiUuid = $request->session()->get('bookingProgramApi')) {
            $bookingProgramApi = ProgramApiRequest::firstWhere('uuid', $bookingProgramApiUuid);
            if ($bookingProgramApi) {
                $bookingProgramApi->booking()
                    ->associate($booking)
                    ->save();
            } else {
                report('Program API by uuid not found. uuid: '.$bookingProgramApiUuid);
            }
        }

        return response()->json(['data' => ['url' => route('booking.step-2', $booking)]]);
    }
}
