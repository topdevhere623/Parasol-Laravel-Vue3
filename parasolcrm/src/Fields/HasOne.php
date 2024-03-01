<?php

namespace ParasolCRM\Fields;

use Closure;
use stdClass;

class HasOne extends RelationField
{
    public string $component = 'HasOne';

    public array $fields = [];

    public $repeaterTitleCallback = null;

    public bool $deletable = false;

    public function setFromRecord($record): self
    {
        $this->value = null;

        $relatedRecord = $record->{$this->relationName}()->first();

        if (!is_null($relatedRecord)) {
            $value = new stdClass();
            $value->id = $relatedRecord->getKey();

            if (!is_null($this->repeaterTitleCallback) && $this->repeaterTitleCallback instanceof Closure) {
                $value->repeaterTitle = call_user_func($this->repeaterTitleCallback, $relatedRecord);
            } elseif (is_null($this->repeaterTitleCallback) || empty($relatedRecord->{$this->repeaterTitleCallback})) {
                $value->repeaterTitle = "{$this->label} #";
            } else {
                $value->repeaterTitle = $relatedRecord->{$this->repeaterTitleCallback};
            }

            foreach ($this->fields as $field) {
                $field->resolveSetFromRecord($relatedRecord);
                $value->{$field->name} = $field->value;
            }

            $this->value = $value;
        }

        return $this;
    }

    public function updateRelated($record)
    {
        if (!isset($this->value)) {
            return;
        }

        $oldRelatedRecords = $record->{$this->relationName}()->get();

        if (!empty($this->value['id'])) {
            $relatedRecord = $oldRelatedRecords->find($this->value['id']);

            if (is_null($relatedRecord)) {
                return;
            }

            if ($this->deletable && !empty($this->value['deleteItem'])) {
                $relatedRecord->delete();

                return;
            }
        } else {
            // TODO: nothing should be deleted
            $record->{$this->relationName}()->delete();

            $relatedRecord = new $this->relatedClass();
        }

        foreach ($this->fields as $field) {
            if (key_exists($field->name, $this->value)) {
                $field->resolveSetValue($this->value[$field->name], $relatedRecord);
            }
            $field->resolveFillRecord($relatedRecord);
        }

        $record->{$this->relationName}()->saveMany([$relatedRecord]);
    }

    public function repeaterTitle($callback): self
    {
        $this->repeaterTitleCallback = $callback;

        return $this;
    }

    public function deletable(): self
    {
        $this->deletable = true;

        $this->withMeta(['deletable' => $this->deletable]);

        return $this;
    }

    public function fields(array $fields): self
    {
        $this->fields = $fields;

        foreach ($fields as $field) {
            if (is_array($field->rules) && $field->rules) {
                $this->rules["{$this->name}.{$field->name}"] = $field->rules;
                $this->rulesAttributes["{$this->name}.{$field->name}"] = strtolower($field->label);

                if (property_exists($field, 'rulesAttributes')) {
                    $this->rulesAttributes = array_merge($this->rulesAttributes, $field->rulesAttributes);
                }
            }
        }

        $this->withMeta([__FUNCTION__ => $fields]);

        return $this;
    }
}
