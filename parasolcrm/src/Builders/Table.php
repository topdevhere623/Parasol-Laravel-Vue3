<?php

declare(ticks=1);

namespace ParasolCRM\Builders;

use Illuminate\Support\Arr;
use ParasolCRM\FieldCollection;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\BelongsToMany;
use ParasolCRM\Fields\HasMany;
use ParasolCRM\Fields\HasOne;
use ParasolCRM\Fields\Hidden;
use ParasolCRM\Fields\ID;
use ParasolCRM\ResourceQuery;
use ParasolCRM\Services\CRM\Facades\Prsl;

class Table extends BaseBuilder
{
    // Table Columns Cache
    protected array $tableColumns = [];

    protected string $sortBy = '';

    protected string $sortDirection = 'desc';

    protected string $perPage = '15';

    public function __construct(ResourceQuery $resourceQuery, FieldCollection $fieldCollection, $defaultSort = [])
    {
        parent::__construct($resourceQuery, $fieldCollection);

        $this->setDefaultSort($defaultSort);
    }

    protected function setDefaultSort($defaultSort = []): void
    {
        $model = $this->resourceQuery->getQueryBuilder()->getModel();
        $this->sortBy = $model->getTable().'.'.$model->getKeyName();

        if (count($defaultSort)) {
            if (key_exists('defaultSortBy', $defaultSort) && $defaultSort['defaultSortBy']) {
                $this->sortBy = $defaultSort['defaultSortBy'];
            }
            if (key_exists('defaultSortDirection', $defaultSort) && $defaultSort['defaultSortDirection']) {
                $this->sortDirection = $defaultSort['defaultSortDirection'];
            }
        }
    }

    protected function setQueryParams(array $params): void
    {
        $queryBuilder = $this->getResourceQuery()->getQueryBuilder();
        $mainTableKey = $queryBuilder->getModel()->getKeyName();

        $params['sortBy'] ??= null;
        $params['sortDirection'] ??= null;

        if ($this->sortBy && !$params['sortBy'] != $this->sortBy) {
            $params['sortDirection'] ??= 'desc';
        }

        if (in_array($params['sortDirection'], ['asc', 'desc'])) {
            $this->sortDirection = $params['sortDirection'];
        }

        if (!empty($params['sortBy']) && $params['sortBy'] != $mainTableKey) {
            $orderField = Arr::first(
                $this->getTableColumns(),
                fn ($item) => $item['key'] == $params['sortBy'] && $item['field']->sortable
            );
            if ($orderField) {
                if (is_string($orderField['field']->sortable)) {
                    $this->sortBy = $orderField['field']->sortable;
                } elseif (isset($orderField['queryColumn'])) {
                    // clear alias (as)
                    $this->sortBy = \Str::afterLast($orderField['queryColumn'], ' as ');
                } else {
                    $mainTable = $queryBuilder->getModel()->getTable();
                    $this->sortBy = $mainTable.'.'.$orderField['field']->column;
                }
            }
        }

        if (empty($params['perPage']) || !in_array($params['perPage'], [15, 30, 50, 100/* 'all' */])) {
            $this->perPage = '15';
        } else {
            $this->perPage = $params['perPage'];
        }
    }

    public function getTableColumns(): array
    {
        if (!$this->tableColumns) {
            $columns = [];

            foreach ($this->fieldCollection->all() as $field) {
                if ($field instanceof HasMany || $field instanceof BelongsToMany) {
                    $columns[] = [
                        'key' => $field->name,
                        'queryColumn' => \DB::raw("count({$field->column}.id) as {$field->column}"),
                        'field' => $field,
                    ];
                } elseif ($field instanceof BelongsTo) {
                    $columns[] = [
                        'key' => $field->name,
                        'queryColumn' => $field->column.'.'.$field->getTitleField().' as '.$field->column.'_title',
                        'field' => $field,
                    ];
                } elseif ($field instanceof HasOne && $field->fields) {
                    foreach ($field->fields as $subField) {
                        if ($subField->displayOnTable) {
                            $columns[] = [
                                'key' => "{$field->name}_{$subField->name}",
                                'queryColumn' => "{$field->column}.{$subField->column} as {$field->column}_{$subField->column}",
                                'label' => "{$field->label} {$subField->label}",
                                'field' => $subField,
                            ];
                        }
                    }
                } else {
                    $columns[] = [
                        'key' => $field->name,
                        'field' => $field,
                    ];
                }
            }

            $this->tableColumns = $columns;
        }

        return $this->tableColumns;
    }

    public function getHeaders(): array
    {
        $result = [];

        foreach ($this->getTableColumns() as $column) {
            $field = $column['field'];

            if ($field instanceof Hidden) {
                continue;
            }

            $result[] = [
                'key' => $column['key'],
                'label' => $field->label,
                'sortable' => $field->sortable,
                'shownOnTable' => $field->shownOnTable,
                'badges' => $field->badges,
                'field' => $field,
            ];
        }

        $result[] = [
            'key' => 'actions',
            'label' => 'Actions',
        ];

        return $result;
    }

    public function download()// : ?StreamedResponse
    {
        return response()->streamDownload(
            function () { // use ($queryBuilder, $columns, $headers)
                $queryBuilder = $this->prepareQuery();
                $params = $this->getParams();
                $selectedColumns = $params['columns'] ?? [];
                $columns = [];
                $headers = [];

                foreach ($this->getTableColumns() as $tableColumn) {
                    if (in_array($tableColumn['key'], $selectedColumns) || !$selectedColumns) {
                        $columns[] = $tableColumn;
                        $headers[] = $tableColumn['field']->label;
                    }
                }

                if (!$columns) {
                    return;
                }

                $file = fopen('php://output', 'w');

                fputcsv($file, $headers, ',');

                $queryBuilder->chunk(100, function ($items) use ($file, $columns) {
                    foreach ($items as $item) {
                        $row = [];

                        foreach ($columns as $column) {
                            $displayValue = $column['field']->resolveDisplayValue($item);
                            if (is_array($displayValue)) {
                                $row[] = $displayValue['text'] ?? $displayValue['value'] ?? '';
                            } else {
                                $row[] = $displayValue;
                            }
                        }
                        fputcsv($file, $row);
                    }
                });

                fclose($file);
            },
            date('Y-m-d_H:i:s_').'.csv'
        );
    }

    public function prepareQuery()
    {
        $queryBuilder = $this->getResourceQuery()->getQueryBuilder();
        $tableColumns = $this->getTableColumns();

        if (!$tableColumns) {
            return [];
        }

        $mainTable = $queryBuilder->getModel()->getTable();
        $mainTableKey = $queryBuilder->getModel()->getKeyName();

        $preColumns = array_filter($tableColumns, fn ($item) => !$item['field']->isComputed);
        $columns
            = array_filter(array_map(fn ($item) => $item['queryColumn'] ?? false, $preColumns), fn ($item) => $item);

        $this->setQueryParams($this->getParams());
        $queryBuilder->addSelect($mainTable.'.*');

        foreach ($this->fieldCollection->getRelationFields() as $relationField) {
            $this->makeRelation($relationField, $queryBuilder);
        }

        if (!$queryBuilder->getQuery()->groups) {
            $queryBuilder->groupBy("{$mainTable}.{$mainTableKey}");
        }
        $queryBuilder->orderBy($this->sortBy, $this->sortDirection);

        if ($this->filter && count($this->filter->getFilters())) {
            $this->filter->applyFilters($queryBuilder);
        }

        $queryBuilder->addSelect($columns);

        // Check if some selects already set earlier (by queryScope etc.)
        // TODO:: check
        // if (!$queryBuilder->getQuery()->columns) {
        //    $queryBuilder->addSelect($columns);
        // } else {
        //    $queryBuilder->addSelect($columns);
        // }

        // $queryBuilder->addSelect("{$mainTable}.{$mainTableKey} as id");

        return $queryBuilder;
    }

    public function getTableItems($getAll = false): array
    {
        $tableColumns = $this->getTableColumns();

        if (!$tableColumns) {
            return [];
        }

        $modelClass = $this->getResourceQuery()->getModelClass();
        $resultItems = [];

        $queryBuilder = $this->prepareQuery();

        $items = $queryBuilder->paginate($this->perPage);

        $policyExists = Prsl::policyExists($modelClass);
        $defaultActions = $policyExists ? [] : [
            'editAccess' => Prsl::checkGatePolicy('update', $modelClass),
            'viewAccess' => Prsl::checkGatePolicy('view', $modelClass),
            'deleteAccess' => Prsl::checkGatePolicy('delete', $modelClass),
            'logAccess' => Prsl::checkGatePolicy('log', $modelClass),
        ];

        foreach ($items as $item) {
            $resultItem = [];
            $resultItem['id'] = $item->getKey();

            foreach ($tableColumns as $tableColumn) {
                $resultItem[$tableColumn['key']] = $tableColumn['field']->resolveDisplayValue($item);
            }

            if ($policyExists) {
                $resultItem['defaultActions'] = [
                    'editAccess' => Prsl::checkGatePolicy('update', $modelClass, $item),
                    'viewAccess' => Prsl::checkGatePolicy('view', $modelClass, $item),
                    'deleteAccess' => Prsl::checkGatePolicy('delete', $modelClass, $item),
                    'logAccess' => Prsl::checkGatePolicy('log', $modelClass, $item),
                ];
            } else {
                $resultItem['defaultActions'] = $defaultActions;
            }

            $resultItems[] = $resultItem;
        }

        return [
            'data' => $resultItems,
            'total' => $items->total(),
            'perPage' => $items->perPage(),
            'currentPage' => $items->currentPage(),
        ];
    }

    public function build(): array
    {
        return array_merge(
            [
                'items' => $this->getTableItems(),
                'headers' => $this->getHeaders(),
                'hasCreateAccess' => Prsl::checkGatePolicy('create', $this->getResourceQuery()->getModelClass()),
                'hasExportAccess' => Prsl::checkGatePolicy('export', $this->getResourceQuery()->getModelClass()),
                'singularLabel' => $this->singularLabel(),
                'label' => $this->label(),

            ],
            $this->params
        );
    }
}
