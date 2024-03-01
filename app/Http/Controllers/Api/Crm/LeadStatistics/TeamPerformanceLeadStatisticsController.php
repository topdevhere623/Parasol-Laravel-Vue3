<?php

namespace App\Http\Controllers\Api\Crm\LeadStatistics;

use App\Http\Controllers\Controller;
use App\Http\Resources\CRM\LeadStatistics\TeamPerformanceLeadStatisticsResource;
use App\Models\BackofficeUserSales;
use App\Models\Lead\CrmPipeline;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadCategory;
use App\Models\Reports\Lead\TeamPerformanceLeadReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use ParasolCRMV2\Builders\Filter;
use ParasolCRMV2\Filters\BetweenFilter;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\NotInFilter;

class TeamPerformanceLeadStatisticsController extends Controller
{
    public function index(): JsonResponse
    {
        abort_unless(\PrslV2::checkGatePolicy('index', TeamPerformanceLeadReport::class), 403, 'Not Allowed');

        $filterBuilder = Filter::make($this->filters());
        $requestFilters = \PrslV2::getRequestFilters();
        $dateFilter = $requestFilters['date'] ?? null;
        $userFilter = $requestFilters['user'] ?? null;

        $filteredQuery = Lead::query()->select('leads.*')
            ->leftJoin('lead_lead_tag', 'lead_lead_tag.lead_id', '=', 'leads.id')
            ->leftJoin('crm_steps', 'crm_steps.id', 'leads.crm_step_id')
            ->groupBy('leads.id');

        $filterBuilder->setValues(\PrslV2::getRequestFilters())->applyFilters($filteredQuery);

        $from = !empty($dateFilter['from']) ? Carbon::parse($dateFilter['from'])->startOfDay() : today()->startOfDay();
        $to = !empty($dateFilter['to']) ? Carbon::parse($dateFilter['to'])->endOfDay() : today()->endOfDay();

        $closedLeadsQuery = $filteredQuery->clone()
            ->where(
                fn ($query) => $query->whereBetween('leads.closed_at', [$from, $to])
            );

        $closedLeadsSummaryQuery = Lead::select('backoffice_user_id')
            ->selectRaw('COUNT(IF(status = "'.Lead::STATUSES['won'].'", 1, NULL)) as won_leads')
            ->selectRaw(
                'COUNT(IF(status IN ("'.Lead::STATUSES['cancelled'].'", "'.Lead::STATUSES['lost'].'"), 1, NULL)) as lost_cancelled_leads'
            )->selectRaw('MAX(IF(status = "'.Lead::STATUSES['won'].'", amount, 0)) as max_amount')->selectRaw(
                'SUM(IF(status = "'.Lead::STATUSES['won'].'", amount, 0)) as total_amount'
            )
            ->fromSub($closedLeadsQuery, 'leads')
            ->groupBy('backoffice_user_id');

        $createdLeadsQuery = $filteredQuery->clone()
            ->where(
                fn ($query) => $query->whereBetween('leads.created_at', [$from, $to])
            );

        $createdLeadsSummaryQuery = Lead::select('created_by')
            ->selectRaw('COUNT(*) as created_leads')
            ->fromSub($createdLeadsQuery, 'leads')
            ->groupBy('created_by');

        $result = BackofficeUserSales::select([
            'backoffice_users.*',
            'won_leads',
            'lost_cancelled_leads',
            'max_amount',
            'total_amount',
            'created_leads',
        ])
            ->leftJoinSub(
                $createdLeadsSummaryQuery,
                'created_leads',
                'backoffice_users.id',
                'created_leads.created_by'
            )
            ->leftJoinSub(
                $closedLeadsSummaryQuery,
                'closed_leads',
                'backoffice_users.id',
                'closed_leads.backoffice_user_id'
            )
            ->when($userFilter, fn ($query) => $query->whereIn('backoffice_users.id', $userFilter))
            ->sales()
            ->groupBy('backoffice_users.id')
            ->orderBy('total_amount', 'desc')
            ->get();

        $summaries = [
            'created_leads' => $result->sum('created_leads'),
            'won_leads' => $result->sum('won_leads'),
            'lost_cancelled_leads' => $result->sum('lost_cancelled_leads'),
            'max_amount' => (int)$result->max('max_amount'),
            'total_amount' => (int)$result->sum('total_amount'),
        ];

        return \Prsl::responseData([
            'items' => TeamPerformanceLeadStatisticsResource::collection($result),
            'summaries' => $summaries,
        ]);
    }

    public function filtersIndex(): JsonResponse
    {
        abort_unless(\PrslV2::checkGatePolicy('index', TeamPerformanceLeadReport::class), 403, 'Not Allowed');

        return \Prsl::responseData(
            Filter::make($this->filters())->build(),
        );
    }

    protected function filters(): array
    {
        $pipelineSteps = $this->getPipelineSteps();
        return [
            BetweenFilter::make(
                DateFilterField::make(today()->subMonth()->addDay()),
                DateFilterField::make(today()),
                'date',
            )
                ->applyHandler(fn () => null)
                ->quick(),
            InFilter::make(
                (new MultipleSelectFilterField())
                    ->options(BackofficeUserSales::getSelectable()),
                'user',
                'leads.backoffice_user_id'
            )
                ->applyHandler(fn () => null)
                ->quick(),
            InFilter::make(
                (new MultipleSelectFilterField())
                    ->options(CrmPipeline::getSelectable()),
                'Pipeline',
                'crm_steps.crm_pipeline_id'
            )->quick(),
            InFilter::make(
                (new MultipleSelectFilterField())
                    ->options($pipelineSteps),
                'Tag',
                'lead_lead_tag.lead_tag_id'
            )->quick(),
            NotInFilter::make(
                (new MultipleSelectFilterField())
                    ->options($pipelineSteps),
                'Without Tag',
                'lead_lead_tag.lead_tag_id'
            )->quick(),
        ];
    }

    protected function getPipelineSteps(): array
    {
        $data = [];
        LeadCategory::with(['leadTags' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->transform(function ($items) use (&$data) {
                $data[$items->id] = [
                    'value' => $items->id,
                    'label' => $items->name,
                    'options' => $items->leadTags
                        ->transform(fn ($step) => [
                            'value' => $step->id,
                            'label' => $step->name,
                        ])
                        ->toArray(),
                ];
            });

        return $data;
    }
}
