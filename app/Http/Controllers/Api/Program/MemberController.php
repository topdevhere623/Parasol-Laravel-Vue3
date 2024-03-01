<?php

namespace App\Http\Controllers\Api\Program;

use App\Enum\Booking\StepEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Program\MemberBillingDetailShowResource;
use App\Http\Resources\Program\MemberBookingShowResource;
use App\Http\Resources\Program\MemberClubShowResource;
use App\Http\Resources\Program\MemberJuniorShowResource;
use App\Http\Resources\Program\MemberKidShowResource;
use App\Http\Resources\Program\MemberPartnerShowResource;
use App\Http\Resources\Program\MemberPrimaryIndexCollection;
use App\Http\Resources\Program\MemberShippingDetailResource;
use App\Http\Resources\Program\MemberShowResource;
use App\Models\Booking;
use App\Models\Country;
use App\Models\Location;
use App\Models\Member\Junior;
use App\Models\Member\Kid;
use App\Models\Member\Member;
use App\Models\Member\MemberBillingDetail;
use App\Models\Member\MemberPrimary;
use App\Models\Member\MemberShippingDetail;
use App\Models\Member\MembershipSource;
use App\Models\Member\Partner;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentMethod;
use App\Models\Payments\PaymentType;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function index()
    {
        $members = MemberPrimary::active()
            ->byProgramId(Auth::id())
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'result' => new MemberPrimaryIndexCollection($members),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|string',
            'photo' => 'required|string',
            'email' => 'required|email',
            'bussiness_email' => 'nullable|email',
            'start_date' => 'required|date',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'total_price' => 'required|numeric',
            'billing' => 'required|array',
            'billing.first_name' => 'required|string',
            'billing.last_name' => 'required|string',
            'billing.company_name' => 'required|string',
            'billing.country' => 'required|string',
            'billing.city' => 'required|string',
            'billing.street' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()], 401);
        }

        $program = Auth::user();

        $api_source = $program->source;
        $api_source_name = $program->name;

        // Check if user exist with this email
        $user = Member::where(['email' => $request->input('email'), 'source' => $api_source])->first();

        if ($user) {
            return response()->json(
                ['status' => 'error', 'message' => 'Member already exist, please contact administration'],
                401
            );
        }

        if ($request->input('partner')) {
            $partner_validator = Validator::make($request->all(), [
                'partner' => 'required|array',
                'partner.photo' => 'required|string',
                'partner.first_name' => 'required|string',
                'partner.last_name' => 'required|string',
                'partner.email' => 'required|email',
                'partner.phone' => 'required|string',
            ]);
            if ($partner_validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $partner_validator->messages()], 401);
            }
        }

        if ($request->input('kids')) {
            $kids_validator = Validator::make($request->all(), [
                'kids' => 'required|array',
                'kids.*.first_name' => 'required|string',
                'kids.*.last_name' => 'required|string',
                'kids.*.birthday' => 'required|date',
            ]);
            if ($kids_validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $kids_validator->messages()], 401);
            }
        }

        if ($request->input('junior')) {
            $junior_validator = Validator::make($request->all(), [
                'junior' => 'required|array',
                'junior.*.first_name' => 'required|string',
                'junior.*.last_name' => 'required|string',
                'junior.*.email' => 'required|string',
                'junior.*.phone' => 'required|string',
                'junior.*.birthday' => 'required|date',
                'junior.*.photo' => 'required|string',
            ]);
            if ($junior_validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $junior_validator->messages()], 401);
            }
        }

        if (!$request->has('billing')) {
            return response()->json(['status' => 'error', 'message' => 'Billing information is required'], 401);
        }

        $plan_title = 'Soleil - single | no children';
        $plan_id = 0;
        $plan_duration = 12;

        if ($request->input('partner') || $request->input('junior')) {
            $plan_title = 'Soleil - couple / family';
        }

        $plan = Plan::with('membershipType')->where('title', $plan_title)->first();

        if ($plan) {
            $plan_id = $plan->id;
            $plan_duration = $plan->getDurationInMonths();
        }

        $user_password = Hash::make(Str::random(8));

        $membershipSource = MembershipSource::getOrCreateMembershipSource(
            $request->input('membership_source_id'),
            $request->input('membership_source_other')
        );
        $membershipSourceId = optional($membershipSource)->id;

        DB::beginTransaction();

        $user = new MemberPrimary();
        $user->avatar = $request->input('photo');
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->phone = $request->input('phone');
        $user->member_type = 'member';
        $user->source = $api_source;
        $user->password = $user_password;
        $user->email = $request->input('email');
        $user->recovery_email = $request->input('bussiness_email');
        $user->main_email = 'personal_email';
        $user->login_email = $request->input('email');
        $user->start_date = $request->input('start_date');
        $user->end_date = date(
            'Y-m-d',
            strtotime('+'.$plan_duration.' months', strtotime($request->input('start_date')))
        );
        $user->membership_status = 'active';
        $user->membershipType()->associate($plan->membershipType);
        $user->plan_id = $plan_id;
        $user->membership_source_id = $membershipSourceId;

        $bookingSnapshotData
            = array_merge(
                [
                    'member' => $request->only(
                        ['first_name', 'last_name', 'phone', 'business_email', 'email', 'start_date']
                    ),
                ],
                $request->only('partner', 'junior', 'kids', 'billing')
            );

        if ($user->save()) {
            $bookingSnapshotData['member']['id'] = $user->id;
            $bookingSnapshotData['member']['membership_source_id'] = $membershipSourceId;
            $bookingSnapshotData['member']['membership_source'] = $api_source_name;
            // Booking
            $booking = new Booking();
            $booking->plan()->associate($plan);
            $booking->name = $request->input('first_name').' '.$request->input('last_name');
            $booking->email = $request->input('email');
            $booking->phone = $request->input('phone');
            $booking->membership_source_id = $membershipSourceId;
            $booking->step = StepEnum::Completed;
            $booking->number_of_children = 0;
            $booking->number_of_juniors = 0;
            $booking->subtotal_amount = $request->input('subtotal');
            $booking->vat_amount = $request->input('tax', 0);
            $booking->total_price = $request->input('total_price');
            $booking->plan_amount = $plan->price;
            $booking->member()->associate($user);
            $booking->save();

            $country_id = Location::HOME_COUNTRY_ID;
            if ($country = Country::where('country_name', $request->input('billing.country'))->first()) {
                $country_id = $country->id;
            }

            $bookingDetails = new MemberBillingDetail();
            $bookingDetails->member_id = $user->id;
            $bookingDetails->first_name = $request->input('billing.first_name');
            $bookingDetails->last_name = $request->input('billing.last_name');
            $bookingDetails->company_name = $request->input('billing.company_name');
            $bookingDetails->country_id = $country_id;
            $bookingDetails->city = $request->input('billing.city');
            $bookingDetails->state = $request->input('billing.state');
            $bookingDetails->street = $request->input('billing.street');
            $bookingDetails->is_gift = false;
            $bookingDetails->save();

            $shippingDetails = new MemberShippingDetail();
            $shippingDetails->member_id = $user->id;
            $shippingDetails->first_name = $request->input('billing.first_name');
            $shippingDetails->last_name = $request->input('billing.last_name');
            $shippingDetails->company_name = $request->input('billing.company_name');
            $shippingDetails->country_id = $country_id;
            $shippingDetails->city = $request->input('billing.city');
            $shippingDetails->state = $request->input('billing.state');
            $shippingDetails->street = $request->input('billing.street');

            $shippingDetails->save();

            // if ($request->has('clubs') && is_array($request->input('clubs'))) {
            $user->activeClubs()->attach($plan->availableClubs()->pluck('id'));
            // }

            if ($request->has('partner')) {
                $booking_partners_info = Partner::create([
                    'parent_id' => $user->id,
                    'avatar' => $request->input('partner.photo'),
                    'first_name' => $request->input('partner.first_name'),
                    'last_name' => $request->input('partner.last_name'),
                    'email' => $request->input('partner.email'),
                    'login_email' => $request->input('partner.email'),
                    'phone' => $request->input('partner.phone'),
                    'start_date' => $user->start_date,
                    'end_date' => $user->end_date,
                    'membership_status' => 'active',
                    'membership_type_id' => optional($plan->membershipType)->id,
                    'plan_id' => $plan_id,
                    'source' => $api_source,
                    'membership_source_id' => $membershipSourceId,
                ]);

                $bookingSnapshotData['partner']['id'] = $booking_partners_info->id;

                $booking_partners_info->activeClubs()->attach($plan->availableClubs()->pluck('id'));
            }
            if ($request->has('junior') && is_array($request->input('junior')) && count($request->input('junior'))) {
                foreach ($request->input('junior') as $key => $junior) {
                    $junior_member_info = Junior::create([
                        'parent_id' => $user->id,
                        'first_name' => Arr::get($junior, 'first_name'),
                        'last_name' => Arr::get($junior, 'last_name'),
                        'email' => Arr::get($junior, 'email'),
                        'phone' => Arr::get($junior, 'phone'),
                        'dob' => Arr::get($junior, 'birthday'),
                        'avatar' => Arr::get($junior, 'photo'),
                        'start_date' => $user->start_date,
                        'end_date' => $user->end_date,
                        'membership_status' => 'active',
                        'membership_type_id' => optional($plan->membershipType)->id,
                        'plan_id' => $plan_id,
                        'source' => $api_source,
                        'membership_source_id' => $membershipSourceId,
                    ]);

                    $bookingSnapshotData['junior'][$key]['id'] = $junior_member_info->id;

                    $junior_member_info->activeClubs()->attach($plan->availableClubs()->pluck('id'));
                }
            }
            if ($request->has('kids') && is_array($request->input('kids')) && count($request->input('kids'))) {
                foreach ($request->input('kids') as $key => $kids) {
                    $kid = Kid::create([
                        'parent_id' => $user->id,
                        'first_name' => Arr::get($kids, 'first_name'),
                        'last_name' => Arr::get($kids, 'last_name'),
                        'dob' => Arr::get($kids, 'birthday'),
                    ]);

                    $bookingSnapshotData['kids'][$key]['id'] = $kid->id;
                }
            }

            $booking->member()->associate($user);
            $booking->save();

            $payment = new Payment();
            $payment->status = Payment::STATUSES['pending'];
            $payment->subtotal_amount = $booking->subtotal_amount;
            $payment->total_amount = $booking->total_price;
            $payment->reference_id = $booking->reference_id;
            $payment->vat_amount = $booking->vat_amount;
            $payment->payment_date = now();

            $payment->member()->associate($user);
            $payment->paymentMethod()->associate(PaymentMethod::ENTERTAINER_PAYMENT_ID);
            $payment->paymentType()->associate(PaymentType::NAME_ID['membership']);
            $payment->saveQuietly();

            $booking->payment()->associate($payment)
                ->save();

            $booking->addSnapshotData($bookingSnapshotData);
            DB::commit();

            return response()->json(
                ['status' => 'success', 'id' => $user->id, 'message' => 'Member successfully added']
            );
        }

        DB::rollBack();

        return response()->json(['status' => 'error', 'message' => 'Please, check your information is correct'], 401);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|string',
            'photo' => 'required|string',
            'bussiness_email' => 'nullable|email',
            'start_date' => 'required|date',
            'billing' => 'required|array',
            'billing.first_name' => 'required|string',
            'billing.last_name' => 'required|string',
            'billing.company_name' => 'required|string',
            'billing.country' => 'required|string',
            'billing.city' => 'required|string',
            'billing.street' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()], 401);
        }

        $user = MemberPrimary::byProgramId(Auth::id())
            ->find($id);

        if (!$user) {
            return response()->json(
                ['status' => 'error', 'message' => 'Member not found, please contact administration'],
                401
            );
        }

        if ($request->input('partner')) {
            $partner_validator = Validator::make($request->all(), [
                'partner' => 'required|array',
                'partner.photo' => 'required|string',
                'partner.first_name' => 'required|string',
                'partner.last_name' => 'required|string',
                'partner.email' => 'required|email',
                'partner.phone' => 'required|string',
            ]);
            if ($partner_validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $partner_validator->messages()], 401);
            }
        }

        if ($request->input('kids')) {
            $kids_validator = Validator::make($request->all(), [
                'kids' => 'required|array',
                'kids.*.first_name' => 'required|string',
                'kids.*.last_name' => 'required|string',
                'kids.*.birthday' => 'required|date',
            ]);
            if ($kids_validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $kids_validator->messages()], 401);
            }
        }

        if ($request->input('junior')) {
            $junior_validator = Validator::make($request->all(), [
                'junior' => 'required|array',
                'junior.*.first_name' => 'required|string',
                'junior.*.last_name' => 'required|string',
                'junior.*.email' => 'required|string',
                'junior.*.phone' => 'required|string',
                'junior.*.birthday' => 'required|date',
                'junior.*.photo' => 'required|string',
            ]);
            if ($junior_validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $junior_validator->messages()], 401);
            }
        }

        DB::beginTransaction();

        $user->avatar = $request->input('photo');
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->phone = $request->input('phone');
        $user->recovery_email = $request->input('bussiness_email');
        $user->start_date = $request->input('start_date');
        $user->end_date = date('Y-m-d', strtotime('+12 months', strtotime($request->input('start_date'))));

        if ($user->save()) {
            if ($request->has('billing')) {
                $country_id = Location::HOME_COUNTRY_ID;
                if ($country = Country::where('country_name', $request->input('billing.country'))->first()) {
                    $country_id = $country->id;
                }
                $bookingDetails
                    = MemberBillingDetail::where('member_id', $user->id)->first() ?? new MemberBillingDetail();
                $bookingDetails->first_name = $request->input('billing.first_name');
                $bookingDetails->last_name = $request->input('billing.last_name');
                $bookingDetails->company_name = $request->input('billing.company_name');
                $bookingDetails->country_id = $country_id;
                $bookingDetails->city = $request->input('billing.city');
                $bookingDetails->state = $request->input('billing.state');
                $bookingDetails->street = $request->input('billing.street');
                $bookingDetails->save();

                $shippingDetails
                    = MemberShippingDetail::where('member_id', $user->id)->first() ?? new MemberShippingDetail();
                $shippingDetails->first_name = $request->input('billing.first_name');
                $shippingDetails->last_name = $request->input('billing.last_name');
                $shippingDetails->company_name = $request->input('billing.company_name');
                $shippingDetails->country_id = $country_id;
                $shippingDetails->city = $request->input('billing.city');
                $shippingDetails->state = $request->input('billing.state');
                $shippingDetails->street = $request->input('billing.street');
                $shippingDetails->save();
            }

            if ($request->has('clubs') && is_array($request->input('clubs'))) {
                $user->activeClubs()->sync($request->input('clubs'));
            }

            if ($request->has('partner') && $partner = $user->partner) {
                $partner->avatar = $request->input('partner.photo');
                $partner->first_name = $request->input('partner.first_name');
                $partner->last_name = $request->input('partner.last_name');
                $partner->email = $request->input('partner.email');
                $partner->login_email = $request->input('partner.email');
                $partner->phone = $request->input('partner.phone');
                $partner->save();

                if ($request->has('clubs') && is_array($request->input('clubs'))) {
                    $partner->activeClubs()->sync($request->input('clubs'));
                }
            }

            if ($request->has('junior') && is_array($request->input('junior')) && count($request->input('junior'))) {
                if (count($user->juniors)) {
                    foreach ($user->juniors as $item) {
                        $item->clubs()->detach();
                        $item->delete();
                    }
                }

                foreach ($request->input('junior') as $junior) {
                    $newJunior = Junior::create([
                        'parent_id' => $user->id,
                        'first_name' => Arr::get($junior, 'first_name'),
                        'last_name' => Arr::get($junior, 'last_name'),
                        'email' => Arr::get($junior, 'email'),
                        'phone' => Arr::get($junior, 'phone'),
                        'dob' => Arr::get($junior, 'birthday'),
                        'avatar' => Arr::get($junior, 'photo'),
                        'member_id' => $request->input('member_id'),
                    ]);

                    if ($request->has('clubs') && is_array($request->input('clubs'))) {
                        $newJunior->activeClubs()->sync($request->input('clubs'));
                    }
                }
            }

            if ($request->has('kids') && is_array($request->input('kids')) && count($request->input('kids'))) {
                Kid::where('parent_id', $user->id)->delete();

                foreach ($request->input('kids') as $kids) {
                    Kid::create([
                        'parent_id' => $user->id,
                        'first_name' => Arr::get($kids, 'first_name'),
                        'last_name' => Arr::get($kids, 'last_name'),
                        'birthday' => Arr::get($kids, 'birthday'),
                        'member_id' => $request->input('member_id'),
                    ]);
                }
            }
            DB::commit();

            return response()->json(['status' => 'success']);
        }

        DB::rollBack();
        return response()->json(['status' => 'error', 'message' => 'Please, check your information is correct'], 401);
    }

    public function show($id)
    {
        $user = MemberPrimary::byProgramId(Auth::id())
            ->where('old_id', $id)
            ->first();

        if ($user) {
            return response()->json([
                'status' => 'success',
                'user' => new MemberShowResource($user),
                'booking' => $user->booking ? new MemberBookingShowResource($user->booking) : [],
                'clubs' => $user->activeClubs->isNotEmpty() ? MemberClubShowResource::collection(
                    $user->activeClubs
                ) : [],
                'kids' => $user->kids->isNotEmpty() ? MemberKidShowResource::collection($user->kids) : [],
                'juniors' => $user->juniors->isNotEmpty() ? MemberJuniorShowResource::collection(
                    $user->juniors
                ) : [],
                'partner' => $user->partner ? new MemberPartnerShowResource($user->partner) : '',
                'billing_detail' => $user->memberBillingDetail ? new MemberBillingDetailShowResource(
                    $user->memberBillingDetail
                ) : '',
                'shipping_detail' => $user->memberShippingDetail ? new MemberShippingDetailResource(
                    $user->memberShippingDetail
                ) : '',
            ]);
        }
        return response()->json(['status' => 'error', 'message' => 'User not found']);
    }

    public function destroy($id)
    {
        $user = MemberPrimary::byProgramId(Auth::id())->find($id);

        if ($user) {
            Booking::where('member_id', $user->id)->delete();

            // TODO: partner is not deleted
            $user->delete();

            return response()->json(['status' => 'success', 'message' => 'Member has been deleted']);
        }

        return response()->json(
            ['status' => 'error', 'message' => 'Member not found, please contact administration'],
            401
        );
    }
}
