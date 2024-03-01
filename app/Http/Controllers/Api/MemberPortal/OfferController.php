<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Http\Resources\MemberPortal\OfferResource;
use App\Http\Resources\MemberPortal\OfferTypeResource;
use App\Models\Offer;
use App\Models\OfferType;
use App\Models\QueryFilters\OfferFilter;

class OfferController extends MemberPortalBaseController
{
    public function index(OfferFilter $filter)
    {
        $this->abortNoAccess('offers');

        $offers = Offer::filter($filter)
            ->active()
            ->with('offerType')
            ->with('activeClubs.city')
            ->orderBy('name')
            ->paginate(config('advplus.default_offer_response_limit'));

        return OfferResource::collection($offers);
    }

    public function show($id)
    {
        $this->abortNoAccess('offers');

        $offer = Offer::active()
            ->with('offerType')
            ->with('activeClubs.city')
            ->with('gallery')
            ->findOrFail($id);

        return new OfferResource($offer);
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
