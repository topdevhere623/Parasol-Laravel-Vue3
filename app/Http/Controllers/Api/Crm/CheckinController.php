<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\Checkin\CheckinMemberActionRequest;
use App\Http\Resources\CRM\Checkin\CheckinMemberResource;
use App\Models\Club\Checkin;
use App\Models\Club\Club;
use App\Models\Member\Member;
use App\Scopes\HSBCProgramAdminScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckinController extends Controller
{
    protected ?Club $club;

    protected ?Member $member = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // TODO: ADD checkin available check
            $this->club = Auth::user()->club;
            return $next($request);
        });
    }

    public function findMember(Request $request): JsonResponse
    {
        $filter = [];

        if ($request->filled('member_id')) {
            $filter['member_id'] = $request->input('member_id');
        } elseif ($request->filled('first_name') && $request->filled('surname')) {
            $filter[] = ['first_name', $request->input('first_name')];
            $filter[] = ['last_name', $request->input('surname')];
        }

        if ($filter) {
            $response = [];
            $response['is_class_checkin_available'] = $this->club->hasClassesSlots();

            $members = Member::where($filter)
                ->with('activeCheckin')
                ->withCount([
                    'clubs',
                ])->when($this->club?->hasKidSlots(), function ($query) use (&$response) {
                    $query->with([
                        'kids' => function ($query) {
                            $query->with('activeCheckins');
                        },
                    ]);

                    $response['kid_slots']
                        = !$this->club->partner->checkin_over_slots ? $this->club->available_kid_slots : 9999;
                })->get();

            abort_if($members->isEmpty(), Response::HTTP_NOT_FOUND, 'Member not found');

            abort_if(
                count($members) > 1,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'More than one member found. Please search by membership number'
            );
            return \Prsl::responseData(
                array_merge($response, ['member' => (new CheckinMemberResource($members->first()))])
            );
        }

        return \Prsl::responseError('Member not found');
    }

    public function checkin(CheckinMemberActionRequest $request, string $type = Checkin::TYPES['regular'])
    {
        $kids = $request->input('kids', []);

        if (!$this->club->partner->checkin_over_slots) {
            abort_if(
                $this->club->available_adult_slots < 1,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'The club is full for checkin'
            );
            abort_if(
                count($kids) > $this->club->available_kid_slots,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'No more slots for kids'
            );
        }

        $member = $this->getMember($request);

        abort_unless(!!$member, Response::HTTP_NOT_FOUND, 'Member not found');

        abort_if(!$member->canCheckin(), Response::HTTP_UNPROCESSABLE_ENTITY, 'Membership on hold');

        abort_unless(
            $member->clubs_count || $member->activeCheckin,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Member is unavailable to check-in'
        );

        $this->createCheckin($request, Checkin::STATUSES['checked_in'], $type);
        \Prsl::responseSuccess('Member successfully checked-in');
    }

    public function checkinClass(CheckinMemberActionRequest $request)
    {
        abort_unless(
            $this->club->getAvailableClassesSlots($this->getMember($request)),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'The member has used all his classes slots for this week'
        );

        $this->checkin($request, Checkin::TYPES['class']);
    }

    public function paidGuestFee(CheckinMemberActionRequest $request)
    {
        $member = $this->getMember($request);

        abort_unless(
            $member->canCheckin(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Member is unavailable to pay guest fee'
        );

        $this->createCheckin($request, Checkin::STATUSES['paid_guest_fee']);
        \Prsl::responseSuccess('Member successfully paid guest fee');
    }

    public function turnedAway(CheckinMemberActionRequest $request)
    {
        $member = $this->getMember($request);
        $status = $member->isExpired() ? Checkin::STATUSES['turned_away_expired'] : Checkin::STATUSES['turned_away'];

        $this->createCheckin($request, $status);

        \Prsl::responseSuccess('Member successfully turned away');
    }

    public function createCheckin(
        CheckinMemberActionRequest $request,
        $status,
        string $type = Checkin::TYPES['regular']
    ) {
        $kids = $request->input('kids', []);

        $member = $this->getMember($request);

        abort_unless(!!$member, Response::HTTP_NOT_FOUND, 'Member not found');

        abort_if(
            $member->activeCheckin,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Member already checked-in'
        );

        $checkin = new Checkin();
        $checkin->status = $status;
        $checkin->type = $type;
        $checkin->number_of_kids = count($kids);
        $checkin->club()->associate($this->club);
        $checkin->member()->associate($member);
        $checkin->save();
        if ($kids) {
            $checkin->kids()->attach($kids);
        }
    }

    public function checkout(Request $request)
    {
        $checkin = Checkin::where('id', $request->input('id'))
            ->active()
            ->first();

        $checkin?->checkout();

        \Prsl::responseSuccess('Member successfully checked-out');
    }

    public function getMember(CheckinMemberActionRequest $request): ?Member
    {
        if ($this->member) {
            return $this->member;
        }

        $member = Member::where('members.id', $request->input('member'))
            ->with('activeCheckin')
            ->withCount([
                'clubs',
            ])
            ->first();

        return $this->member = $member;
    }

    public function filterClubsOptions(): ?array
    {
        if (Auth::user()->hasTeam('adv_management')) {
            return Club::pluck('title', 'id')
                ->transform(function ($option, $index) {
                    return ['value' => $index, 'text' => $option];
                })->values()
                ->toArray();
        } elseif (Auth::user()->hasTeam('program_admins')) {
            $query = Checkin::select(['clubs.title', 'clubs.id'])->joinRelation('club')
                ->joinRelation('member');

            (new HSBCProgramAdminScope())->apply($query, $query->getModel());

            return $query
                ->pluck('title', 'id')
                ->transform(function ($option, $index) {
                    return ['value' => $index, 'text' => $option];
                })->values()
                ->toArray();
        }

        return null;
    }
}
