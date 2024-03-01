<?php

namespace ParasolCRM\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use ParasolCRM\Fields\Selectable;

/**
 * Class EqualFilter
 *
 * @package ParasolCRM\Filters
 */
class EqualCascadeFilter extends Filter
{
    use Selectable;

    protected ?Filter $child = null;

    /**
     * Filter handler
     *
     * @param  Builder  $builder
     * @param $value
     *
     * @return void
     */
    public function apply(Builder $builder, $value): void
    {
        if (Arr::has($value, $this->name)) {
            $builder->where($this->column, Arr::get($value, $this->name));

            if ($this->child instanceof EqualCascadeFilter) {
                $this->deepApply($builder, $value, $this->child);
            } else {
                if (Arr::has($value, $this->child->name)) {
                    $builder->where($this->child->column, Arr::get($value, $this->child->name));
                }
            }
        }
    }

    protected function deepApply(Builder $builder, $value, $child)
    {
        $builder->where($child->column, Arr::get($value, $child->name));

        if ($child->child instanceof EqualCascadeFilter) {
            $this->deepApply($builder, $value, $child->child);
        } else {
            if (Arr::has($value, $child->name)) {
                $builder->where($child->column, Arr::get($value, $child->name));
            }
        }
    }

    public function child(Filter $child): self
    {
        $this->child = $child;

        $this->withMeta([
            'child' => $this->child,
        ]);

        return $this;
    }
}
