<?php

namespace App\Jobs\Plecto;

use App\Enum\Booking\StepEnum;
use App\Models\Booking;
use App\Services\PlectoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class PushBookingPlectoJob extends BasePushPlectoJob implements ShouldQueue
{
    public function handle(PlectoService $plectoService)
    {
        if (!$plectoService->isAvailable()) {
            return;
        }

        Booking::where('step', StepEnum::Completed)
            ->when($this->ids, fn ($query) => $query->whereIn('id', $this->ids))
            ->with('lead.backofficeUser', 'plan.package.program')
            ->with(
                'plan',
                fn ($query) => $query->withTrashed()->with(
                    'package',
                    fn ($query) => $query->withTrashed()->with('program')
                )
            )
            ->whereHas('lead')
            ->chunk(
                100,
                function (Collection $collection) use ($plectoService) {
                    $data = [];
                    $collection->each(function (Booking $item) use (&$data) {
                        $data[] = [
                            'data_source' => PlectoService::DATA_SOURCES[Booking::class],
                            'member_api_provider' => 'advplus-system',
                            'member_api_id' => $item->lead->backofficeUser->id,
                            'member_name' => $item->lead->backofficeUser->full_name,
                            'external_id' => $item->id,
                            'date' => $item->last_step_changed_at->toIso8601String(),
                            'reference_id' => $item->reference_id,
                            'step' => $item->step,
                            'type' => $item->type,
                            'name' => $item->name,
                            'total_price' => $item->total_price,
                            'plan' => $item->plan?->title,
                            'package' => $item->plan->package->title,
                            'program' => $item->plan->package->program->name,
                            'units' => $item->units,
                        ];
                    });
                    $plectoService->pushData($data);
                }
            );
    }
}
