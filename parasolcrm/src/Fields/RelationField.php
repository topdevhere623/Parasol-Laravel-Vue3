<?php

namespace ParasolCRM\Fields;

use Closure;
use Illuminate\Database\Eloquent\Model;

abstract class RelationField extends Field
{
    public $record;

    protected string $relatedClass;

    protected string $relationName;

    public array $rulesAttributes = [];

    protected ?Closure $updateRelatedHandlerCallback = null;

    public function __construct($relationName, $relatedClass, $name = null, $label = null, $attrs = null)
    {
        $name ??= $relationName;
        parent::__construct($name, $label, $attrs);

        $this->relationName = $relationName;

        $this->relatedClass = $relatedClass;
    }

    public function getRelationName(): string
    {
        return $this->relationName;
    }

    public function getRelatedClass(): string
    {
        return $this->relatedClass;
    }

    public function updateRelated($record)
    {
        return $this;
    }

    public function updateRelatedHandler(Closure $callback): self
    {
        $this->updateRelatedHandlerCallback = $callback;
        return $this;
    }

    public function resolveUpdateRelated(Model $record): self
    {
        if ($this->fillableRecord) {
            if (!is_null($this->updateRelatedHandlerCallback)) {
                call_user_func($this->updateRelatedHandlerCallback, $record, $record->{$this->relationName}(), $this);
            } else {
                $this->updateRelated($record);
            }
        }

        return $this;
    }

    public function fields(array $fields): self
    {
        $this->fields = $fields;

        foreach ($fields as $field) {
            if (is_array($field->rules) && $field->rules) {
                $this->rules["{$this->name}.*.{$field->name}"] = $field->rules;
                $this->rulesAttributes["{$this->name}.*.{$field->name}"] = strtolower($field->label);

                if (property_exists($field, 'rulesAttributes')) {
                    $this->rulesAttributes = array_merge($this->rulesAttributes, $field->rulesAttributes);
                }
            }
        }

        $this->withMeta([__FUNCTION__ => $fields]);

        return $this;
    }

    public function fillRecord($record): self
    {
        return $this;
    }
}
