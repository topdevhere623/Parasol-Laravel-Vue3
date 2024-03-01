<?php

namespace ParasolCRM\Statuses;

use Illuminate\Database\Eloquent\Builder;

class DoughnutStatus extends Status
{
    public string $component = 'StatusChartDoughnut';

    protected array $labels = [];
    protected array $colors = [];

    public function resolveData(Builder $builder): self
    {
        if ($this->query && is_callable($this->query)) {
            $builder = call_user_func($this->query, $builder);
        }

        if (is_callable($this->executor)) {
            $result = call_user_func($this->executor, $builder);
        } else {
            throw new \Exception('Executor not found for stat: '.$this->title);
        }
        $total = $result->sum('value');

        $result->each(function (&$item) use ($total) {
            $item->label = $this->labels[$item->group_by] ?? $item->group_by;
            $item->color = $this->colors[$item->group_by] ?? null;
            $item->percent = number_format($total ? $item->value / $total * 100 : 0, 2);
        });

        $this->withMeta(['total' => $total]);

        $this->data = $result;

        return $this;
    }

    public function count(
        string $groupBy,
        string $fieldName = null,
        ?string $orderBy = null,
        bool $orderDesc = false
    ): self {
        $this->executor = function (Builder $builder) use ($groupBy, $fieldName, $orderBy, $orderDesc) {
            $fieldName ??= "{$builder->getModel()->getTable()}.id";

            return $builder->select(
                \DB::raw($groupBy.' as group_by'),
                \DB::raw("count(DISTINCT {$fieldName}) as value")
            )
                ->when($orderBy, fn (Builder $builder) => $builder->orderBy($orderBy, $orderDesc ? 'desc' : 'asc'))
                ->groupBy('group_by')
                ->get();
        };

        return $this;
    }

    public function sum($groupBy, $column): self
    {
        $this->executor = function ($builder) use ($groupBy, $column) {
            return $builder->select(\DB::raw($groupBy.' as group_by'), \DB::raw('sum('.$column.') as value'))
                ->sortBy('value')
                ->groupBy('group_by')
                ->get();
        };

        return $this;
    }

    public function labels($labels): self
    {
        $this->labels = $labels;

        return $this;
    }

    public function colors($colors): self
    {
        $this->colors = $colors;

        return $this;
    }
}
