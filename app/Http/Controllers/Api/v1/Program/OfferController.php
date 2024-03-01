<?php

namespace App\Http\Controllers\Api\v1\Program;

use App\Http\Resources\v1\Program\Offer\OfferDetailsResource;
use App\Http\Resources\v1\Program\Offer\OfferResource;
use App\Http\Resources\v1\Program\Offer\OfferTypeResource;
use App\Models\Offer;
use App\Models\OfferType;
use App\Models\QueryFilters\OfferFilter;
use Illuminate\Http\JsonResponse;

class OfferController extends BaseProgramApiController
{
    public function index(OfferFilter $filter): JsonResponse
    {
        $offers = Offer::filter($filter)
            ->where('offers.status', Offer::STATUSES['active'])
            ->with('offerType')
            ->with('activeClubs.city')
            ->orderBy('name')
            ->paginate(static::PER_PAGE);

        return response()->json([
            'data' => [
                'per_page' => $offers->perPage(),
                'page' => $offers->currentPage(),
                'total' => $offers->total(),
                'last_page' => $offers->lastPage(),
                'items' => OfferResource::collection($offers)->resolve(),
            ],
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $offer = Offer::where('offers.status', Offer::STATUSES['active'])
            ->with('offerType', 'activeClubs', 'gallery')
            ->where('uuid', $uuid)
            ->first();

        if (!$offer) {
            return response()->json(['message' => 'Offer not found']);
        }

        return OfferDetailsResource::make($offer)->response();
    }

    public function offerTypes()
    {
        $offerTypes = OfferType::whereHas('activeOffers')->get();

        return OfferTypeResource::collection($offerTypes);
    }

    public function offerEmirates()
    {
        /** TODO: need to get rid of this method of obtaining data */
        return response()->json([
            'data' => Offer::active()
                ->groupBy('emirate')
                ->pluck('emirate')
                ->all(),
        ]);
    }
}
