<?php

namespace ParasolCRMV2\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\Filters\Fields\FilterField;

class BetweenFilter extends Filter
{
    public FilterField $field;

    public FilterField $field2;

    public string $component = 'BetweenField';

    public function __construct(
        FilterField $field,
        FilterField $field2,
        string $name,
        string $column = null,
        string $label = null
    ) {
        $this->field2 = $field2;
        parent::__construct($field, $name, $column, $label);
    }

    public function apply(Builder $builder, $value): void
    {
        if (is_array($value)) {
            $from = $value['from'] ?? false;
            $to = $value['to'] ?? false;

            if ($from) {
                $fieldFrom = $this->field->setValue($from)->getValue();

                if ($this->field instanceof DateFilterField) {
                    $fieldFrom = optional(Carbon::make($fieldFrom))->startOfDay();
                }

                if (!is_null($fieldFrom)) {
                    $builder->where($this->column, '>=', $fieldFrom);
                }
            }
            if ($to) {
                $fieldTo = $this->field2->setValue($to)->getValue();

                if ($this->field2 instanceof DateFilterField) {
                    $fieldTo = optional(Carbon::make($fieldTo))->endOfDay();
                }

                if (!is_null($fieldTo)) {
                    $builder->where($this->column, '<=', $fieldTo);
                }
            }
        }
    }

    public function getDefaultValue()
    {
        if ($this->field->getDefaultValue() || $this->field2->getDefaultValue()) {
            return [
                'from' => $this->field->getDefaultValue(),
                'to' => $this->field2->getDefaultValue(),
            ];
        }
    }

    public function jsonSerialize(): array
    {
        return array_merge(
            [
                'component' => $this->component,
                'name' => $this->name,
                'label' => $this->label,
                'isQuick' => $this->isQuick,
                'field1' => $this->field,
                'field2' => $this->field2,
            ],
            $this->getMeta()
        );
    }
}
