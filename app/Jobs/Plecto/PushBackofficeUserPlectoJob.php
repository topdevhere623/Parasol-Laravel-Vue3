<?php

namespace App\Jobs\Plecto;

use App\Models\BackofficeUser;
use App\Services\PlectoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class PushBackofficeUserPlectoJob extends BasePushPlectoJob implements ShouldQueue
{
    public function handle(PlectoService $plectoService)
    {
        if (!$plectoService->isAvailable()) {
            return;
        }

        BackofficeUser::when($this->ids, fn ($query) => $query->whereIn('id', $this->ids))
            ->whereHasTeam(BackofficeUser::TEAM)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'sales');
            })
            ->chunk(
                100,
                function (Collection $collection) use ($plectoService) {
                    $data = [];
                    $collection->each(function (BackofficeUser $item) use (&$data) {
                        $data[] = [
                            'data_source' => PlectoService::DATA_SOURCES[BackofficeUser::class],
                            'member_api_provider' => 'advplus-system',
                            'member_api_id' => $item->id,
                            'member_name' => $item->full_name,
                            'external_id' => today()->format('Ym').$item->id,
                            'date' => today()->startOfWeek(),
                            'first_name' => $item->first_name,
                            'last_name' => $item->last_name,
                            'sales_units_target' => $item->sales_units_target,
                            'renewal_target_percent' => $item->renewal_target_percent,
                            'weekly_sales_units_target' => $item->weekly_sales_units_target,
                            'weekly_renewal_target_percent' => $item->weekly_renewal_target_percent,
                            'sales_revenue_target' => $item->sales_revenue_target,
                            'weekly_sales_revenue_target' => $item->weekly_sales_revenue_target,
                        ];
                    });
                    $plectoService->pushData($data);
                }
            );
    }
}
