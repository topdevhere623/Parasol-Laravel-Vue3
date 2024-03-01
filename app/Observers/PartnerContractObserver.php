<?php

namespace App\Observers;

use App\Models\Partner\PartnerContract;

class PartnerContractObserver
{
    public function saving(PartnerContract $model): void
    {
        if ($model->expiry_date && $model->expiry_date?->endOfDay()?->isPast()) {
            $model->status = PartnerContract::STATUSES['expired'];
        }

        if ($model->status == PartnerContract::STATUSES['pending']
            && $model->start_date && $model->start_date?->startOfDay()?->isPast()
        ) {
            $model->status = PartnerContract::STATUSES['active'];
        }
    }

    public function saved(PartnerContract $model): void
    {
        $model->partner
            ->calculateSlots()
            ->save();
    }

    public function deleted(PartnerContract $model): void
    {
        $model->partner
            ->calculateSlots()
            ->save();
    }
}
