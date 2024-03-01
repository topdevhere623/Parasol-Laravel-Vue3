<?php

declare(ticks=1);

namespace ParasolCRM\Builders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ParasolCRM\Containers\Container;
use ParasolCRM\Containers\Group;
use ParasolCRM\FieldCollection;
use ParasolCRM\Fields\RelationField;
use ParasolCRM\ResourceQuery;
use ParasolCRM\Services\CRM\Facades\Prsl;

class Form extends BaseBuilder
{
    protected array $layout = [];
    protected array $layoutFields = [];
    protected Model $record;

    public function __construct(ResourceQuery $resourceQuery, FieldCollection $fieldCollection, Model $record, $layout = [])
    {
        parent::__construct($resourceQuery, $fieldCollection);

        $this->setRecord($record);
        $this->layoutFields = $fieldCollection->getLayoutFields();
        $this->layout = $layout;
    }

    public function getValues($newRecord = false): array
    {
        if (!$newRecord && isset($this->record)) {
            $record = $this->getRecord();
            $this->getFieldCollection()->setValuesFromRecord($record);
            $values['id'] = $record->getKey();
        }

        foreach ($this->fieldCollection->all() as $field) {
            if ($newRecord) {
                $field->setValueToDefault();
            }

            $values[$field->name] = $field->getValue();
        }

        return $values ?? [];
    }

    public function getLayout($layout = []): array
    {
        $layout = $this->getAccessLayout(count($layout) ? $layout : $this->layout);
        $layout = count($layout) ? $layout : $this->getDefaultLayout();

        foreach ($layout as $key => $layoutItem) {
            $deepLayout = $this->deepLayout($layoutItem);
            if (!$deepLayout) {
                unset($layout[$key]);
            }
        }

        return $layout;
    }

    public function getDefaultLayout(): array
    {
        $layout = [];
        foreach ($this->layoutFields as $field) {
            if (property_exists($field, 'fields')) {
                foreach ($field->fields as $item) {
                    $layout[] = $item->name;
                }
            } else {
                $layout[] = $field->name;
            }
        }
        $this->layout = [Group::make()->attach($layout)];
        return $this->layout;
    }

    private function deepLayout($layoutItem)
    {
        if ($layoutItem instanceof Container) {
            foreach ($layoutItem->children as $key => &$child) {
                $deepLayoutItem = $this->deepLayout($child);
                if ($deepLayoutItem) {
                    $child = $deepLayoutItem;
                } else {
                    unset($layoutItem->children[$key]);
                }
            }
            return count($layoutItem->children) ? $layoutItem : null;
        }
        return $this->findLayoutFieldByName($layoutItem);
    }

    private function findLayoutFieldByName(string $layoutItem)
    {
        if (strpos($layoutItem, '.')) {
            $item = explode('.', $layoutItem);
            $relation = current($item);
            $field = next($item);

            foreach ($this->layoutFields as $key => $relationLayoutField) {
                if ($relationLayoutField->name === $relation) {
                    if ($relationLayoutField instanceof RelationField) {
                        foreach ($relationLayoutField->fields as $itemKey => $layoutField) {
                            if ($layoutField->name === $field) {
                                unset($this->layoutFields[$key]->fields[$itemKey]);
                                return $layoutField;
                            }
                        }
                    }
                }
            }
        } else {
            foreach ($this->layoutFields as $key => $layoutField) {
                if ($layoutField->name === $layoutItem) {
                    unset($this->layoutFields[$key]);
                    return $layoutField;
                }
            }
        }
    }

    private function getAccessLayout($layout): array
    {
        return array_filter($layout, fn ($item) => $item->checkHasAccess());
    }

    public function build($newRecord = false): array
    {
        return [
            'singularLabel' => $this->singularLabel(),
            'model' => $this->getValues($newRecord),
            'form' => $this->getLayout(),
            'defaultActions' => $this->getDefaultActions($newRecord),
        ];
    }

    protected function getDefaultActions($newRecord): array
    {
        if ($newRecord) {
            return [];
        }

        $modelClass = $this->getResourceQuery()->getModelClass();
        return [
            'editAccess' => Prsl::checkGatePolicy('update', $modelClass, $this->getRecord()),
            'viewAccess' => Prsl::checkGatePolicy('view', $modelClass, $this->getRecord()),
            'deleteAccess' => Prsl::checkGatePolicy('delete', $modelClass, $this->getRecord()),
            'logAccess' => Prsl::checkGatePolicy('log', $modelClass, $this->getRecord()),
        ];
    }

    public function fillRecord(): self
    {
        foreach ($this->fieldCollection->all() as $field) {
            $field->resolveFillRecord($this->getRecord());
        }

        return $this;
    }

    public function save(): bool
    {
        return !!DB::transaction(function () {
            $this->fillRecord();
            $this->getRecord()->save();
            $this->getFieldCollection()->updateRelation($this->getRecord());

            $this->getFieldCollection()->setValuesFromRecord($this->getRecord()->fresh());

            return true;
        });
    }

    public function setRecord(Model $record): void
    {
        $this->record = $record;
    }

    public function getRecord(): Model
    {
        return $this->record;
    }

    public function setParams(array $params): self
    {
        $this->params = $params;

        $this->getFieldCollection()->setValuesFromArray($params, $this->getRecord());

        return $this;
    }

    public function delete(): bool
    {
        return $this->getRecord()->delete();
    }
}
