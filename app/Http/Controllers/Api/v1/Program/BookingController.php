<?php

namespace App\Http\Controllers\Api\v1\Program;

use App\Enum\Booking\StepEnum;
use App\Http\Requests\Program\BookingRequest;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BookingController extends BaseProgramApiController
{
    public function remote(BookingRequest $request): JsonResponse
    {
        /** @var Program $program */
        $program = \Auth::user();

        $apiRequest = $program->programApiRequest()
            ->updateOrCreate(
                ['external_id' => $request->external_id],
                ['request' => $request->validated()]
            );

        if ($request->package_id) {
            $package = $program->activePackages()
                ->where('uuid', $request->package_id)
                ->first();
        } else {
            $package = $program->apiDefaultPackage;
        }

        if (!$package) {
            report(new \Exception('Program API default package not found. Requested uuid:'.$request->package_id));
            return response()->json(['message' => 'Package not found'], Response::HTTP_NOT_FOUND);
        }

        if ($apiRequest->booking && in_array($apiRequest->booking->step, [StepEnum::MembershipDetails, StepEnum::BillingDetails, StepEnum::Completed])) {
            $url = route('booking.step-'.$apiRequest->booking->step->getOldValue(), $apiRequest->booking);
        } else {
            $url = route('booking.step-1', [
                'package' => $package->slug,
                'request_id' => $apiRequest->uuid,
            ]);
        }

        return response()->json(['data' => ['url' => $url]]);
    }
}
