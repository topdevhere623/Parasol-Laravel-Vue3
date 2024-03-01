<?php

namespace App\Jobs\Plecto;

use App\Models\BackofficeUser;
use App\Models\Member\MembershipRenewal;
use App\Services\PlectoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class PushMembershipRenewalPlectoJob extends BasePushPlectoJob implements ShouldQueue
{
    public function handle(PlectoService $plectoService)
    {
        if (!$plectoService->isAvailable()) {
            return;
        }

        $defaultSales = BackofficeUser::find(81);

        MembershipRenewal::when($this->ids, fn ($query) => $query->whereIn('id', $this->ids))
            ->chunk(
                100,
                function (Collection $collection) use ($plectoService, $defaultSales) {
                    $data = [];
                    $collection->each(function (MembershipRenewal $item) use (&$data, $defaultSales) {
                        $data[] = [
                            'data_source' => PlectoService::DATA_SOURCES[MembershipRenewal::class],
                            'member_api_provider' => 'advplus-system',
                            'member_api_id' => $item->member?->lead?->backofficeUser?->id ?? $defaultSales->id,
                            'member_name' => $item->member?->lead?->backofficeUser?->full_name ?? $defaultSales->full_name,
                            'external_id' => $item->id,
                            'date' => $item->end_date->toIso8601String(),
                            'status' => $item->status,
                            'membership_number' => $item->member->member_id,
                        ];
                    });
                    $plectoService->pushData($data);
                }
            );
    }
}
