<?php

namespace App\Http\Controllers\Api\Crm\LeadStatistics;

use App\Http\Resources\CRM\LeadStatistics\CompanyPerformanceLeadStatisticsResource;
use App\Models\BackofficeUserSales;
use App\Models\Lead\CrmPipeline;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadCategory;
use App\Models\Lead\LeadTag;
use App\Models\Reports\Lead\CompanyPerformanceLeadReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use ParasolCRMV2\Builders\Chart;
use ParasolCRMV2\Builders\Filter;
use ParasolCRMV2\Charts\ChartDateInterval;
use ParasolCRMV2\Filters\BetweenFilter;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\SelectFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\NotInFilter;

class CompanyPerformanceLeadStatisticsController extends TeamPerformanceLeadStatisticsController
{
    use ChartDateInterval;

    protected $dateformats = [
        'hour' => '%Y-%m-%d %H',
        'day' => '%Y-%m-%d',
        'week' => '%x-%v',
        'custom_week' => '%x-%v',
    ];

    public function index(): JsonResponse
    {
        abort_unless(\PrslV2::checkGatePolicy('index', CompanyPerformanceLeadReport::class), 403, 'Not Allowed');

        $filterBuilder = Filter::make($this->filters());
        $requestFilters = \PrslV2::getRequestFilters();
        $dateFilter = $requestFilters['date'] ?? null;
        $userFilter = $requestFilters['user'] ?? null;
        $requestFilters['group_by'] ??= 'date';

        $filteredQuery = Lead::query()->select('leads.*')
            ->leftJoin('lead_lead_tag', 'lead_lead_tag.lead_id', '=', 'leads.id')
            ->leftJoin('crm_steps', 'crm_steps.id', 'leads.crm_step_id')
            ->groupBy('leads.id');

        $filterBuilder->setValues(\PrslV2::getRequestFilters())->applyFilters($filteredQuery);

        $from = !empty($dateFilter['from']) ? Carbon::parse($dateFilter['from'])->startOfDay() : today()->subYear(
        )->startOfDay();
        $to = !empty($dateFilter['to']) ? Carbon::parse($dateFilter['to'])->endOfDay() : today()->endOfDay();

        $closedLeadsQuery = $filteredQuery->clone()
            ->where(
                fn ($query) => $query->whereBetween('leads.closed_at', [$from, $to])
            )
            ->when($userFilter, fn ($query) => $query->whereIn('leads.backoffice_user_id', $userFilter));

        $createdLeadsQuery = $filteredQuery->clone()
            ->where(
                fn ($query) => $query->whereBetween('leads.created_at', [$from, $to])
            )
            ->when($userFilter, fn ($query) => $query->whereIn('leads.created_by', $userFilter));

        if ($requestFilters['group_by'] === 'date') {
            $this->startDate = $from;
            $this->endDate = $to;

            $scale = $requestFilters['scale'] ?? 'month';

            if ($scale == 'week' && $from->diffInWeeks($to) > 20) {
                $scale = 'custom_week';
            }

            $this->setInterval($scale ?? 'month');
            $this->getLabel = true;

            $this->fillIntervals();
            $dateFormat = Chart::$dateFormats[$this->getIntervalName()];

            $closedLeadsSummaryQuery = Lead::selectRaw('DATE_FORMAT(leads.closed_at, "'.$dateFormat.'") as date')
                ->selectRaw('COUNT(IF(status = "'.Lead::STATUSES['won'].'", 1, NULL)) as won_leads')
                ->selectRaw(
                    'COUNT(IF(status IN ("'.Lead::STATUSES['cancelled'].'", "'.Lead::STATUSES['lost'].'"), 1, NULL)) as lost_cancelled_leads'
                )->selectRaw('MAX(IF(status = "'.Lead::STATUSES['won'].'", amount, 0)) as max_amount')->selectRaw(
                    'SUM(IF(status = "'.Lead::STATUSES['won'].'", amount, 0)) as total_amount'
                )
                ->fromSub($closedLeadsQuery, 'leads')
                ->groupBy('date');
            $createdLeadsSummaryQuery = Lead::selectRaw('DATE_FORMAT(leads.created_at, "'.$dateFormat.'") as date')
                ->selectRaw('COUNT(*) as created_leads')
                ->fromSub($createdLeadsQuery, 'leads')
                ->groupBy('date');

            $result = Lead::select([
                'leads.date_formatted',
                'leads.date',
                'lost_cancelled_leads',
                'max_amount',
                'total_amount',
                'created_leads',
            ])
                ->selectRaw('IFNULL(won_leads, 0) as won_leads')
                ->fromSub(
                    $createdLeadsQuery->clone()->select([])->selectRaw(
                        'DATE_FORMAT(leads.created_at, "'.$dateFormat.'") as date, leads.created_at as raw_date, DATE_FORMAT(leads.created_at, "%d %b %Y") as date_formatted'
                    )
                        ->unionAll(
                            $closedLeadsQuery->clone()->select([])->selectRaw(
                                'DATE_FORMAT(leads.closed_at, "'.$dateFormat.'") as date, leads.closed_at as raw_date, DATE_FORMAT(leads.closed_at, "%d %b %Y") as date_formatted'
                            )
                        ),
                    'leads'
                )
                ->leftJoinSub(
                    $createdLeadsSummaryQuery,
                    'created_leads',
                    'leads.date',
                    'created_leads.date'
                )
                ->leftJoinSub(
                    $closedLeadsSummaryQuery,
                    'closed_leads',
                    'leads.date',
                    'closed_leads.date'
                )
                ->withTrashed()
                ->groupBy('date')
                ->orderBy('raw_date')
                ->get();

            $summaries = [
                'created_leads' => $result->sum('created_leads'),
                'won_leads' => $result->sum('won_leads'),
                'lost_cancelled_leads' => $result->sum('lost_cancelled_leads'),
                'max_amount' => (int)$result->max('max_amount'),
                'total_amount' => (int)$result->sum('total_amount'),
            ];

            $chart = [
                'labels' => $this->labels,
                'lines' => [
                    'won_leads' => array_values(
                        array_merge($this->intervals, $result->pluck('won_leads', 'date')->toArray())
                    ),
                    'total_amount' => array_values(
                        array_merge($this->intervals, $result->pluck('total_amount', 'date')->toArray())
                    ),
                ],
            ];
        } else {
            $summaries = null;
            $result = LeadTag::select([
                'lead_tags.name',
            ])
                ->selectRaw('COUNT(closed_leads.id) as created_leads')
                ->selectRaw('COUNT(IF(closed_leads.status = "'.Lead::STATUSES['won'].'", 1, NULL)) as won_leads')
                ->selectRaw(
                    'COUNT(IF(closed_leads.status IN ("'.Lead::STATUSES['cancelled'].'", "'.Lead::STATUSES['lost'].'"), 1, NULL)) as lost_cancelled_leads'
                )->selectRaw(
                    'MAX(IF(closed_leads.status = "'.Lead::STATUSES['won'].'", closed_leads.amount, 0)) as max_amount'
                )->selectRaw(
                    'SUM(IF(closed_leads.status = "'.Lead::STATUSES['won'].'", closed_leads.amount, 0)) as total_amount'
                )
                ->leftJoin('lead_lead_tag', 'lead_lead_tag.lead_tag_id', '=', 'lead_tags.id')
                ->leftJoinSub(
                    $createdLeadsQuery,
                    'created_leads',
                    'lead_lead_tag.lead_id',
                    'created_leads.id'
                )
                ->leftJoinSub(
                    $closedLeadsQuery,
                    'closed_leads',
                    'lead_lead_tag.lead_id',
                    'closed_leads.id'
                )
                ->where('lead_category_id', $requestFilters['group_by'])
                ->withTrashed()
                ->groupBy('lead_tags.id')
                ->orderBy('lead_tags.name')
                ->get();

            $chart = [
                'labels' => $result->pluck('name'),
                'lines' => [
                    'won_leads' => $result->pluck('won_leads'),
                    'total_amount' => $result->pluck('total_amount'),
                ],
            ];
        }

        return \Prsl::responseData([
            'items' => CompanyPerformanceLeadStatisticsResource::collection($result),
            'summaries' => $summaries,
            'chart' => $chart,
        ]);
    }

    public function filtersIndex(): JsonResponse
    {
        abort_unless(\PrslV2::checkGatePolicy('index', CompanyPerformanceLeadReport::class), 403, 'Not Allowed');

        return \Prsl::responseData(
            Filter::make($this->filters())->build(),
        );
    }

    protected function filters(): array
    {
        $pipelineSteps = $this->getPipelineSteps();
        return [
            BetweenFilter::make(
                DateFilterField::make(today()->subYear()),
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
            EqualFilter::make(
                SelectFilterField::make()->default('date')
                    ->options(['date' => 'Date'] + LeadCategory::getSelectable()->toArray()),
                'group_by',
            )->applyHandler(fn () => null)
                ->quick(),
        ];
    }
}
