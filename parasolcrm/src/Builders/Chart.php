<?php

declare(ticks=1);

namespace ParasolCRM\Builders;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Charts\Chart as ChartField;
use ParasolCRM\FieldCollection;
use ParasolCRM\ResourceQuery;

class Chart extends BaseBuilder
{
    /** @var array */
    public array $charts = [];

    /** @var ChartField $chart */
    protected ChartField $chart;

    /** @var string */
    protected string $dateformat = 'DAY';

    /** @var string[] */
    protected $dateformats = [
        'HOUR' => '%Y-%m-%d %H',
        'DAY' => '%Y-%m-%d',
        'WEEK' => '%Y-%m-%d',
    ];

    public function __construct(ResourceQuery $resourceQuery, FieldCollection $fieldCollection, array $charts)
    {
        parent::__construct($resourceQuery, $fieldCollection);

        $this->charts = $charts;
    }

    /**
     * @return array
     * @throws \ErrorException
     */
    public function build(): array
    {
        $charts = [];

        foreach ($this->charts as $chart) {
            // @var ChartField $chart
            $this->chart = $chart;
            $queryBuilder = $chart->model ? $chart->model::query() : $this->getResourceQuery()->getQueryBuilder();

            foreach ($this->fieldCollection->getRelationFields() as $relationField) {
                $this->makeRelation($relationField, $queryBuilder);
            }

            if ($this->filter && count($this->filter->getFilters())) {
                $this->filter->applyFilters($queryBuilder);
            }

            $this->setChartCustomData($queryBuilder);

            $charts[$this->chart->name] = $chart->chart($this->getChartData($queryBuilder));
        }

        return $charts;
    }

    /**
     * Set chart dates and interval
     *
     * @throws \ErrorException
     */
    protected function setChartCustomData(Builder $queryBuilder): void
    {
        if ($this->chart->groupColumn) {
            if ($this->filter && count($this->filter->getFilters())) {
                $filter = $this->filter->findFilter($this->chart->groupColumn);
            }

            $params = $this->getParams()['filters'] ?? [];
            if (isset($filter) && is_array($filter) && key_exists($filter->name, $params)) {
                if (is_array($params[$filter->name])) {
                    $startDate = $params[$filter->name]['from'];
                    $endDate = $params[$filter->name]['to'];
                } else {
                    $startDate = $params[$filter->name];
                    $endDate = $params[$filter->name];
                }
            } else {
                $dateRange = $queryBuilder->clone()->selectRaw(
                    "min({$this->chart->groupColumn}) as min, max({$this->chart->groupColumn}) as max"
                )->first();

                $startDate = $dateRange->min ?? date('Y-m-d');
                $endDate = $dateRange->max ?? date('Y-m-d');
            }

            if (!$startDate && !$endDate) {
                throw new \ErrorException('Default dates not added.');
            }

            $startDate = is_object($startDate) ? $startDate : Carbon::parse($startDate);
            $this->chart->startDate = $startDate->startOfHour();

            $endDate = is_object($endDate) ? $endDate : Carbon::parse($endDate);
            $this->chart->endDate = $endDate->endOfHour();

            $this->dateformat = $this->chart->setInterval();
        }
    }

    /**
     * Get chart data
     *
     * @return array
     */
    protected function getChartData(Builder $queryBuilder): array
    {
        if ($this->chart->groupColumn) {
            $groupColumn = $this->chart->groupColumn;
            $groupColumnFormatted = "{$groupColumn}_formatted";

            $queryBuilder->selectRaw(
                "DATE_FORMAT({$groupColumn},
            '{$this->dateformats[$this->dateformat]}') as {$groupColumnFormatted}"
            );

            $queryBuilder->groupBy($groupColumnFormatted);
            $queryBuilder->orderBy($groupColumn);
        }

        $data = [];
        foreach ($this->chart->columnsData as $column => $item) {
            $builder = clone $queryBuilder;
            $case = $column;
            $alias = $item['alias'] ?? $column;

            if (isset($item['case'])) {
                $col = current($item['case']);
                $operator = next($item['case']);
                $val = next($item['case']);
                $val = "'{$val}'";
                if (strtoupper($operator) === 'BETWEEN') {
                    $secondVal = next($item['case']);
                    $val = "{$val} AND '{$secondVal}'";
                }
                $case = "CASE WHEN {$col} {$operator} {$val} THEN 1 END";
            }

            $builder->selectRaw('IFNULL('.strtoupper($item['action'])."({$case}) ,0) AS {$alias}");
            $data[$column] = $builder->get()->pluck($alias, $groupColumnFormatted ?? '')->toArray();
        }

        return $data;
    }
}
