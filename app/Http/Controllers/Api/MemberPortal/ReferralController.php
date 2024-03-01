<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Http\Requests\Referral\ChooseRewardRequest;
use App\Http\Requests\Referral\StoreRequest;
use App\Http\Resources\MemberPortal\Referrals\ReferralResource;
use App\Http\Resources\MemberPortal\Referrals\ReferralRewardClubResource;
use App\Http\Resources\MemberPortal\Referrals\ReferralRewardOptionResource;
use App\Jobs\CashbackRewardMailJob;
use App\Jobs\Lead\CreateFromReferralLeadJob;
use App\Models\Member\Member;
use App\Models\Referral;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ReferralController extends MemberPortalBaseController
{
    public function index()
    {
        $this->abortNoAccess('referrals');
        return ReferralResource::collection(Auth::user()->referrals);
    }

    public function store(StoreRequest $request)
    {
        $this->abortNoAccess('referrals');

        $referral = Referral::create([
            ...$request->validated(),
            'status' => Referral::STATUSES['lead'],
            'member_id' => Auth::user()->getPrimaryMemberId(),
        ]);

        report_if(!$referral, new \Exception('Unable to create referral'));

        CreateFromReferralLeadJob::dispatch($referral);

        return response(
            status: $referral ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST
        );
    }

    public function chooseReward(string $uuid, ChooseRewardRequest $request): JsonResponse
    {
        $this->abortNoAccess('referrals');

        /** @var Member $member */
        $member = Auth()->user();
        /** @var Referral $referral */
        $referral = $member
            ->referrals()
            ->where('uuid', $uuid)
            ->rewardAvailable()
            ->firstOrFail();

        $reward = $request->reward;
        if ($reward == Referral::REWARDS['additional_month']) {
            $users = array_merge([$member], $member->activeJuniors->all());
            if ($partner = $member->activePartner) {
                $users[] = $partner;
            }
            foreach ($users as $user) {
                $user->update([
                    'end_date' => Carbon::parse($user->end_date)->addMonth(),
                ]);
            }
        } elseif ($reward == Referral::REWARDS['additional_club']) {
            \DB::beginTransaction();
            $additionalReferrals = $member->referrals()
                ->rewardAvailable()
                ->latest()
                ->where('id', '!=', $referral->id)
                ->limit(2)
                ->get();
            if (!$additionalReferrals || $additionalReferrals->count() < 2) {
                return response()->json(
                    ['message' => 'You don\'t have enough referrals to add an additional club'],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            $additionalReferrals->each(
                fn (Referral $additionalReferral) => $additionalReferral->update([
                    'reward' => $reward,
                    'reward_status' => Referral::REWARD_STATUSES['complete'],
                ])
            );
            $club = $member->activeUnusedClubs()
                ->where('uuid', $request->club)
                ->first();
            if (!$club) {
                return response()->json(
                    ['message' => 'Club is not available in your plan'],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            $member->clubs()->save($club);
            \DB::commit();
        }

        $notes = $referral->notes;
        if ($reward === Referral::REWARDS['cashback']) {
            $notes .= sprintf(
                "\nBank name: %s\nAccount name: %s\nIBAN: %s\nSWIFT, BIC or routing code: %s\nCurrency: %s\n",
                $request->bank_name,
                $request->account_name,
                $request->iban,
                $request->swift,
                $request->currency
            );
        }

        $referral->update([
            'reward' => $reward,
            'reward_status' => $reward === Referral::REWARDS['cashback'] ? Referral::REWARD_STATUSES['pending'] : Referral::REWARD_STATUSES['complete'],
            'notes' => $notes,
        ]);

        dispatch(new CashbackRewardMailJob($referral));

        return response()->json(['message' => 'Reward successfully chosen']);
    }

    public function rewardOptions(): ReferralRewardOptionResource
    {
        $this->abortNoAccess('referrals');

        return new ReferralRewardOptionResource(Auth::user());
    }

    public function rewardClubs(): AnonymousResourceCollection
    {
        $this->abortNoAccess('referrals');

        return ReferralRewardClubResource::collection(Auth::user()->activeUnusedClubs());
    }
}
