<?php

namespace App\Observers;

use App\Models\Offer;

class OfferObserver
{
    public function saving(Offer $offer)
    {
        if ($offer->expiry_date && $offer->expiry_date->endOfDay()->isPast()) {
            $offer->status = Offer::STATUSES['expired'];
        }
    }
}
