<?php

declare(ticks=1);

namespace ParasolCRMV2\Builders;

use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Builders\Interfaces\ComponentBuilder;
use ParasolCRMV2\Containers\Container;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Filters\Filter as FilterExecutor;
use ParasolCRMV2\Makeable;

class Filter implements ComponentBuilder
{
    use Makeable;

    protected array $filters = [];
    protected array $filterCache = [];
    protected array $layoutFilters = [];
    protected array $filterValues = [];

    public function __construct(array $filters, array $layoutFilters = [])
    {
        $this->filters = $filters;
        $this->layoutFilters = $layoutFilters;
    }

    public function getFilters(): array
    {
        if (!$this->filterCache) {
            $this->filterCache = array_filter(
                $this->filters,
                function ($field) {
                    return $field->checkHasAccess();
                }
            );
        }
        return $this->filterCache;
    }

    public function setValues($filterValues): self
    {
        $this->filterValues = $filterValues;
        return $this;
    }

    public function applyFilters(Builder $queryBuilder)
    {
        foreach ($this->filterValues as $name => $value) {
            $filter = $this->findFilter($name);
            $value = $this->clearFilterValue($value);

            if ($filter && ($value != '')) {
                $filter->resolveApply($queryBuilder, $value);
            }
        }
    }

    /**
     * Clear empty string or array in filter values
     */
    protected function clearFilterValue($value)
    {
        if (is_array($value) && count($value)) {
            return array_filter($value);
        }
        if (is_string($value)) {
            return trim($value);
        }
        return $value;
    }

    public function findFilter(string $name): ?FilterExecutor
    {
        foreach ($this->getFilters() as $filter) {
            if ($filter->name === $name) {
                return $filter;
            }
        }
        return null;
    }

    public function getQuickFilters(): array
    {
        return array_filter(
            $this->getFilters(),
            function (FilterExecutor $filter) {
                return $filter->isQuick;
            }
        );
    }

    public function getLayoutFilters(): array
    {
        $layout = $this->layoutFilters;

        $otherFields = array_filter(
            $this->getFilters(),
            function (FilterExecutor $filter) {
                return !$filter->isHidden;
            }
        );

        foreach ($layout as $element) {
            $this->deepLayoutFilters($element, $otherFields);
        }

        if ($otherFields) {
            $defaultGroup = new Group();
            $defaultGroup->children = array_values($otherFields);
            $layout[] = $defaultGroup;
        }

        return $layout;
    }

    private function deepLayoutFilters(Container $container, &$otherFields): Container
    {
        foreach ($container->children as $key => &$child) {
            if ($child instanceof Container) {
                $child = $this->deepLayoutFilters($child, $otherFields);
            } else {
                $child = $this->findFilter($child);

                if (is_null($child)) {
                    unset($container->children[$key]);
                    continue;
                }

                foreach ($otherFields as $k => $otherField) {
                    if ($otherField == $child) {
                        unset($otherFields[$k]);
                    }
                }
            }
        }

        return $container;
    }

    public function getDefaultFilterValues(): array
    {
        $values = [];
        foreach ($this->getFilters() as $filter) {
            $default = $filter->getDefaultValue();
            if (!is_null($default)) {
                $values[$filter->name] = $default;
            }
        }

        return $values;
    }

    public function build(): array
    {
        return [
            'defaultFilterValues' => $this->getDefaultFilterValues() ?: null,
            'filters' => $this->getLayoutFilters(),
            'quickFilters' => $this->getQuickFilters(),
        ];
    }
}
