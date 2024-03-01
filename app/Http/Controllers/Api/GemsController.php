<?php

namespace App\Http\Controllers\Api;

use App\Enum\Booking\StepEnum;
use App\Http\Controllers\Controller;
use App\Models\GemsApi;
use App\Models\Member\Member;
use App\Models\Package;
use App\Models\Program;
use App\Services\GemsApiService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GemsController extends Controller
{
    public function __invoke(Request $request, GemsApiService $gemsMembersService)
    {
        $requestData = $this->getDecryptedRequest($gemsMembersService);
        // dd($requestData);
        if ($requestData->mem_adv_plus_id) {
            $auth = $this->authMemberPortal($requestData);
            return $auth ?: response()->json(
                ['status' => 'error', 'message' => 'Access Denied'],
                Response::HTTP_UNAUTHORIZED
            );
        } elseif ($requestData->loyal_id) {
            return $this->booking($requestData);
        }

        return response()->json(['status' => 'error', 'message' => 'Access Denied'], Response::HTTP_UNAUTHORIZED);
    }

    protected function booking($requestData)
    {
        $gemsApi = GemsApi::with('booking')
            ->latest('booking_id')
            ->whereLoyalId($requestData->loyal_id)
            ->first();

        if (optional($gemsApi)->booking) {
            $booking = $gemsApi->booking;

            if ($booking->step == StepEnum::Completed) {
                return redirect()->route(
                    'booking.step-4',
                    $booking,
                    Response::HTTP_MOVED_PERMANENTLY
                );
            }

            if (in_array($booking->step, StepEnum::afterPaymentSteps())) {
                return redirect()->route(
                    'booking.step-'.$booking->step->getOldValue(),
                    $booking,
                    Response::HTTP_MOVED_PERMANENTLY
                );
            }
        } else {
            $gemsApi = new GemsApi();

            $gemsApi->request = (array)$requestData;
            $gemsApi->loyal_id = $requestData->loyal_id;
            $gemsApi->save();
        }
        // TODO: Refactor this
        $package = Package::whereId($requestData->user_type == 'friends_and_family' ? 53 : 26)->active()->first();

        return redirect()->route('booking.step-1', [
            'package' => $package->slug,
            'gems_uuid' => $gemsApi->uuid,
        ], Response::HTTP_MOVED_PERMANENTLY);
    }

    protected function authMemberPortal($requestData)
    {
        $program = Program::whereSource(Program::SOURCE_MAP['gems'])->first(['id']);

        if (!$program) {
            report('GEMs program not found');
            return false;
        }

        $member = Member::whereProgramId($program->id)
            ->where('member_id', $requestData->mem_adv_plus_id)
            ->first();

        if ($member && $token = optional($member->createToken('Gems Member Portal'))->accessToken) {
            return redirect(\URL::member('authenticate', ['token' => $token]), Response::HTTP_MOVED_PERMANENTLY);
        }

        return false;
    }

    protected function getDecryptedRequest(GemsApiService $gemsMembersService)
    {
        $requestDecryptFields = [
            'loyal_id',
            'aff_id',
            'token_id',
            'first_name',
            'last_name',
            'Mem_Adv_plus_Id',
            'user_type',
            'partner_email',
            'partner_phone',
        ];

        $requestData = [];

        foreach ($requestDecryptFields as $decryptField) {
            // Replace "space" by "plus" symbol used because "plus" transforms to "space" in query params
            // For some reason GEMS Team doesn't prepare values for query string
            try {
                $requestData[strtolower($decryptField)] = request()->has($decryptField) && request($decryptField)
                    ? $gemsMembersService->decryptString(
                        str_replace(' ', '+', request($decryptField))
                    ) : null;
            } catch (\Exception $exception) {
                report($exception);
            }
        }

        return (object)$requestData;
    }
}
