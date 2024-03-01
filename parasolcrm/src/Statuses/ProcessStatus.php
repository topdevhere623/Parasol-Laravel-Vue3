<?php

namespace ParasolCRM\Statuses;

use Illuminate\Database\Eloquent\Builder;

class ProcessStatus extends Status
{
    public string $component = 'StatusProcess';

    protected $currentValue;
    protected $targetValue;
    protected $color;

    public function resolveData(Builder $builder): self
    {
        if ($this->query && is_callable($this->query)) {
            $builder = call_user_func($this->query, $builder);
        }

        if (is_callable($this->currentValue)) {
            $currentValue = call_user_func($this->currentValue, $builder->clone());
        } else {
            $currentValue = $this->currentValue;
        }

        if (is_callable($this->targetValue)) {
            $targetValue = call_user_func($this->targetValue, $builder->clone());
        } else {
            $targetValue = $this->targetValue;
        }

        if (is_callable($this->color)) {
            $color = call_user_func($this->color, $builder->clone());
        } else {
            $color = $this->color;
        }

        $percent = number_format($targetValue ? $currentValue / $targetValue * 100 : 0, 2);

        $this->withMeta(['current_value' => $currentValue]);
        $this->withMeta(['target_value' => $targetValue]);
        $this->withMeta(['percent' => $percent]);
        $this->withMeta(['color' => $color]);
        return $this;
    }

    public function currentValue($value): self
    {
        $this->currentValue = $value;
        return $this;
    }

    public function currentTitle($value): self
    {
        $this->withMeta(['current_title' => $value]);
        return $this;
    }

    public function targetValue($value): self
    {
        $this->targetValue = $value;
        return $this;
    }

    public function targetTitle($value): self
    {
        $this->withMeta(['target_title' => $value]);
        return $this;
    }

    public function color($color): self
    {
        $this->color = $color;
        return $this;
    }
}
