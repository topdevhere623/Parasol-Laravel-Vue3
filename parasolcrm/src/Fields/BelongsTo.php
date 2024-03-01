<?php

namespace ParasolCRM\Fields;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BelongsTo extends RelationField
{
    use RelationSelectable;
    use Selectable;

    public string $component = 'SelectField';
    protected ?string $defaultEndpoint;
    protected ?Closure $optionHandlerCallback = null;

    public function __construct($relationName, $relatedClass, $name = null, $label = null, $attrs = null)
    {
        parent::__construct($relationName, $relatedClass, $name, $label, $attrs);
        $this->endpoint(\Prsl::getRelationFieldEndpoint($this->name));
    }

    public function fillRecord($record): self
    {
        $record->{$this->relationName}()->associate($this->value);
        return $this;
    }

    public function displayValue($record)
    {
        // Cuz of Selectable trait
        return $record->{$this->column.'_title'};
    }

    public function setFromRecord($record): self
    {
        // Get value for form or for table
        $this->value = $record->getAttribute($record->{$this->relationName}()->getForeignKeyName());

        return $this;
    }

    public function getOptions(Model $record, $filter = null)
    {
        $relatedModel = $record->{$this->relationName}()->getRelated();

        if ($this->optionHandlerCallback) {
            $collection = call_user_func($this->optionHandlerCallback, $relatedModel::query()->orderBy($this->titleField), $record, $this);
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

    public function optionHandler(Closure $callback): self
    {
        $this->optionHandlerCallback = $callback;
        return $this;
    }
}
