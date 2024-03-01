<?php

namespace ParasolCRM;

use ParasolCRM\Fields\Hidden;
use ParasolCRM\Fields\RelationField;
use ParasolCRM\Fields\UploadGroup;

class FieldCollection
{
    use Makeable;

    public array $fields = [];

    private array $fieldsCache = [];

    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    public function findFieldByName(string $name)
    {
        foreach ($this->all() as $field) {
            if ($field->name === $name) {
                return $field;
            }
        }
    }

    public function findRelationFieldByClassName(string $name)
    {
        foreach ($this->all() as $field) {
            if ($field->getRelatedClass() === $name) {
                return $field;
            }
        }
    }

    public function all(): array
    {
        if (!$this->fieldsCache) {
            $this->fieldsCache = array_filter(
                $this->fields,
                function ($field) {
                    return $field->checkHasAccess();
                }
            );
        }
        return $this->fieldsCache;
    }

    public function allCollection(): FieldCollection
    {
        if (!$this->fieldsCache) {
            return new self(
                $this->fieldsCache = array_filter(
                    $this->fields,
                    function ($field) {
                        return $field->checkHasAccess();
                    }
                )
            );
        }
        return new self($this->fieldsCache);
    }

    public function getTableFields(): FieldCollection
    {
        return new self(
            array_filter(
                $this->all(),
                function ($field) {
                    return $field->displayOnTable;
                }
            )
        );
    }

    public function getFormFields(): FieldCollection
    {
        return new self(
            array_filter(
                $this->all(),
                function ($field) {
                    return $field->displayOnForm;
                }
            )
        );
    }

    public function getLayoutFields(): array
    {
        return array_filter(
            $this->all(),
            function ($field) {
                return $field->displayOnForm && !$field instanceof Hidden;
            }
        );
    }

    public function getRelationFields(): array
    {
        return array_filter(
            $this->all(),
            function ($field) {
                return $field instanceof RelationField;
            }
        );
    }

    public function setValuesFromRecord($record): void
    {
        if ($record !== null) {
            array_map(fn ($field) => $field->actionTypeUpdate()->resolveSetFromRecord($record), $this->all());
        }
    }

    public function setValuesFromArray($values, $record): void
    {
        foreach ($values as $fieldName => $value) {
            if ($field = $this->findFieldByName($fieldName)) {
                if ($field instanceof UploadGroup) {
                    $field->record = $record;
                }
                $field->resolveSetValue($value, $record);
            }
        }
    }

    public function updateRelation($record): void
    {
        foreach ($this->all() as $field) {
            if ($field instanceof RelationField) {
                $field->resolveUpdateRelated($record);
            }
        }
    }

    //    public function fillLabelsByModel($model)
    //    {
    //        each $field->label(ParasolCRMService::($model))
    //    }
}
