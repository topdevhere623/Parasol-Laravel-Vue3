<?php

namespace App\Observers;

use App\Models\Partner\PartnerContract;
use App\Models\Partner\PartnerTranche;

class PartnerTrancheObserver
{
    public function saving(PartnerTranche $model): void
    {
        $partnerContract = $model->partnerContract;

        if ($partnerContract->access_type == PartnerContract::ACCESS_TYPES['prepaid']) {
            $model->adult_slots = $partnerContract->family_membership_adults_per_slot * $model->family_membership_count + $model->single_membership_count;
            $model->kid_slots = $partnerContract->family_membership_kids_per_slot * $model->family_membership_count
                + $partnerContract->single_membership_kids_per_slot * $model->single_membership_count
                + $model->individual_kid_membership_count;

            if ($model->status == PartnerTranche::STATUSES['awaiting_first_visit']) {
                $model->start_date = null;
                $model->expiry_date = null;
            } else {
                $model->start_date = $partnerContract->start_date;
                $model->expiry_date = $partnerContract->expiry_date;
            }
        }

        if ($partnerContract->access_type == PartnerContract::ACCESS_TYPES['postpaid']) {
            $model->single_membership_count = 0;
            $model->family_membership_count = 0;
            $model->individual_kid_membership_count = 0;
        }

        if ($model->status == PartnerTranche::STATUSES['awaiting_first_visit']) {
            $model->start_date = null;
            $model->expiry_date = null;
        }

        if ($model->expiry_date && $model->expiry_date?->endOfDay()?->isPast()) {
            $model->status = PartnerTranche::STATUSES['expired'];
        }

        if ($model->status == PartnerTranche::STATUSES['pending']
            && $model->start_date && $model->start_date?->startOfDay()?->isPast()
        ) {
            $model->status = PartnerTranche::STATUSES['active'];
        }
    }

    public function saved(PartnerTranche $model): void
    {
        $model->partner
            ->calculateSlots()
            ->save();
    }

    public function deleted(PartnerTranche $model): void
    {
        if ($model->partner) {
            $model->partner
                ->calculateSlots()
                ->save();
        }
    }
}
