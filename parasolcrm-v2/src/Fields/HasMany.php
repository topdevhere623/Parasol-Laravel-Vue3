<?php

namespace ParasolCRMV2\Fields;

use Closure;
use stdClass;

// TODO: validation
class HasMany extends RelationField
{
    public string $component = 'Repeater';

    public array $fields = [];

    public string|Closure|null $repeaterTitleCallback = null;

    public function setFromRecord($record): self
    {
        $this->value = [];

        if ($this->fields) {
            if ($relatedRecords = $record->{$this->relationName}) {
                foreach ($relatedRecords->all() as $key => $relatedRecord) {
                    $value = new stdClass();
                    $value->id = $relatedRecord->getKey();

                    if ($this->repeaterTitleCallback instanceof Closure) {
                        $value->repeaterTitle = call_user_func($this->repeaterTitleCallback, $relatedRecord);
                    } elseif (is_null(
                        $this->repeaterTitleCallback
                    ) || empty($relatedRecord->{$this->repeaterTitleCallback})) {
                        $value->repeaterTitle = "{$this->label} #".++$key.'';
                    } else {
                        $value->repeaterTitle = $relatedRecord->{$this->repeaterTitleCallback};
                    }

                    foreach ($this->fields as $field) {
                        $field->resolveSetFromRecord($relatedRecord);
                        $value->{$field->name} = $field->value;
                    }
                    $this->value[] = $value;
                }
            }
        }

        return $this;
    }

    public function updateRelated($record)
    {
        $this->record = $record;

        $oldRelatedRecords = $this->record->{$this->relationName}()->get();

        $relatedRecords = [];
        if (isset($this->value)) {
            foreach ($this->value as $val) {
                if (!empty($val['id'])) {
                    $relatedRecord = $oldRelatedRecords->find($val['id']);
                    if (!$relatedRecord) {
                        continue;
                    }
                    if (!empty($val['deleteItem'])) {
                        $relatedRecord->delete();
                        continue;
                    }
                } else {
                    $relatedRecord = new $this->relatedClass();
                }

                foreach ($this->fields as $field) {
                    if (key_exists($field->name, $val)) {
                        $field->resolveSetValue($val[$field->name], $relatedRecord);
                    }
                    $field->fillRecord($relatedRecord);
                }

                $relatedRecords[] = $relatedRecord;
            }
        }

        if (count($relatedRecords)) {
            $this->record->{$this->relationName}()->saveMany($relatedRecords);
        }
    }

    public function repeaterTitle(string|Closure|null $callback): self
    {
        $this->repeaterTitleCallback = $callback;

        return $this;
    }

    public function disableAddMore($disable = true): self
    {
        $this->withMeta(['disable_add_more' => $disable]);

        return $this;
    }

    public function disableDelete($disable = true): self
    {
        $this->withMeta(['disable_delete' => $disable]);

        return $this;
    }
}
