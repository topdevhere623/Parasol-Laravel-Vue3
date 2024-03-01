<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\CRM\Lead\LeadKanbanResource;
use App\Models\Lead\CrmPipeline;
use App\Models\Lead\CrmStep;
use App\Models\Lead\Lead;
use App\ParasolCRMV2\Resources\LeadResource;
use Illuminate\Http\Request;
use ParasolCRMV2\Builders\Filter;
use ParasolCRMV2\Http\Requests\Kanban\MoveCardRequest;
use ParasolCRMV2\Services\CRM\Facades\PrslV2;

class KanbanController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(PrslV2::checkGatePolicy('index', Lead::class), 403, 'Not Allowed');

        $resultItems = [];
        $total = 0;
        $lastPage = 0;
        $perPage = 10;
        $currentPage = $request->get('page', 1);

        $filters = \PrslV2::getRequestFilters();
        $pipelineId = $filters['pipeline'] ?? CrmPipeline::first()?->id;
        $stepIds = CrmStep::query()->where('crm_pipeline_id', $pipelineId)->pluck('id');
        list($query, $sortBy, $sortDirection) = $this->getKanbanLeadQueryData();

        if ($request->has('mobile')) {
            $data = $query->whereIn('leads.crm_step_id', $stepIds)
                ->paginate(perPage: $perPage, page: $currentPage);

            $resultItems = LeadKanbanResource::collection($data->items());

            $total = $data->total();
            $lastPage = $data->lastPage();
        } else {
            foreach ($stepIds as $stepId) {
                $data = (clone $query)
                    ->where('leads.crm_step_id', $stepId)
                    ->paginate(perPage: $perPage, page: $currentPage);

                $resultItems[$stepId] = [
                    'data' => LeadKanbanResource::collection($data->items()),
                    'total' => $data->total(),
                    'lastPage' => $data->lastPage(),
                ];

                $total += $data->total();
                $lastPage = max($lastPage, $data->lastPage());
            }
        }

        return [
            'data' => $resultItems,
            'total' => $total,
            'perPage' => $perPage,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'sorts' => $this->leadKanbanSorts(),
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
        ];
    }

    public function steps(Request $request)
    {
        $filters = \PrslV2::getRequestFilters();
        $pipelineId = $filters['pipeline'] ?? CrmPipeline::first()?->id;

        return CrmStep::query()
            ->whereHas(
                'crmPipeline',
                fn ($q) => $q->where('id', $pipelineId)
            )
            ->pluck('name', 'id');
    }

    public function moveCard(MoveCardRequest $request, int $id)
    {
        $cardItem = Lead::findOrFail($id);

        abort_unless(!!$cardItem, 404, 'Not found');
        abort_unless(PrslV2::checkGatePolicy('update', Lead::class, $cardItem), 403, 'Not Allowed');

        $cardItem->fill($request->validated());
        $cardItem->save();

        return $cardItem;
    }

    private function getKanbanLeadQueryData()
    {
        $resource = new LeadResource();
        $filters = \PrslV2::getRequestFilters();

        $leadQuery = Lead::query()
            ->withCount('crmComments')
            ->with(['crmStep', 'booking.member']);
        $resource->query($leadQuery);

        $leadQuery->addSelect('leads.*');

        if (!empty($filters)) {
            $resourceFilters = Filter::make($resource->filters());
            $resourceFilters->setValues($filters)->applyFilters($leadQuery);
        }

        // sort
        $sortBy = request()->input('sortBy', array_key_first($this->leadKanbanSorts()));
        $sortDirection = request()->input('sortDirection', 'desc');
        if (isset($this->leadKanbanSorts()[$sortBy])) {
            foreach ($this->leadKanbanSorts()[$sortBy]['columns'] as $sortColumn) {
                $leadQuery->orderBy($sortColumn, $sortDirection);
            }
        }

        $leadQuery->groupBy('leads.id');

        return [$leadQuery, $sortBy, $sortDirection];
    }

    private function leadKanbanSorts()
    {
        return [
            'updated' => [
                'label' => 'Last update',
                'columns' => ['leads.updated_at'],
            ],
            'next_action' => [
                'label' => 'Next action',
                'columns' => ['leads.remind_date', 'leads.remind_time'],
            ],
            'created' => [
                'label' => 'Creation date',
                'columns' => ['leads.created_at'],
            ],
            'amount' => [
                'label' => 'Amount',
                'columns' => ['leads.amount'],
            ],
            'alphabetically' => [
                'label' => 'Alphabetically',
                'columns' => ['leads.title', 'leads.first_name', 'leads.last_name'],
            ],
            'closed' => [
                'label' => 'Closed date',
                'columns' => ['leads.closed_at'],
            ],
        ];
    }
}
