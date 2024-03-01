<?php

namespace ParasolCRM\Fields;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BelongsToMany extends RelationField
{
    use RelationSelectable;
    use Selectable;

    public string $component = 'TagField';

    public bool $displayOnTable = false;

    protected ?Closure $optionHandlerCallback = null;

    public function __construct($relationName, $relatedClass, $name = null, $label = null, $attrs = null)
    {
        parent::__construct($relationName, $relatedClass, $name, $label, $attrs);
        $this->endpoint(\Prsl::getRelationFieldEndpoint($this->name));
    }

    public function setComponent(string $component): self
    {
        $this->component = $component;

        return $this;
    }

    public function setFromRecord($record): self
    {
        $relation = $record->{$this->relationName};
        $this->value = $relation ? $relation->pluck($this->valueField) : '';

        return $this;
    }

    public function updateRelated($record): void
    {
        $relation = $record->{$this->relationName}();
        $relation->sync($this->getIds());
    }

    public function getIds(): array
    {
        $values = [];

        if (is_array($this->value)) {
            foreach ($this->value as $value) {
                $values[] = !empty($value['id']) ? $value['id'] : $value;
            }
        }

        return $values;
    }

    // Because of Selectable Trait
    public function displayValue($record)
    {
        return $record->{$this->column};
    }

    public function getOptions(Model $record, $filter = null)
    {
        $relatedModel = $record->{$this->relationName}()->getRelated();

        if ($this->optionHandlerCallback) {
            $collection = call_user_func(
                $this->optionHandlerCallback,
                $relatedModel::query()->orderBy($this->titleField),
                $record,
                $this
            );
            if (is_array($collection)) {
                $collection = collect($collection);
            }
            if ($collection instanceof Builder) {
                $collection = $collection->pluck($this->titleField, $this->valueField);
            }
        } else {
            $collection = $relatedModel::query()->orderBy($this->titleField)
                ->pluck($this->titleField, $this->valueField);
        }

        return $collection->transform(function ($option, $index) {
            return ['value' => $index, 'text' => $option];
        })
            ->values()
            ->toArray();
    }

    protected function getMergeWhereTableName($relationName, $column, $pivotTableName): string
    {
        if (strpos($column, '.')) {
            $columnData = explode('.', $column);
            if (current($columnData) != $pivotTableName) {
                return $relationName.'.'.next($columnData);
            }
        }
        return $relationName.'_'.$column;
    }

    public function optionHandler(Closure $callback): self
    {
        $this->optionHandlerCallback = $callback;
        return $this;
    }
}
