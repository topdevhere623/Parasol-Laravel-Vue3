<?php

namespace ParasolCRM;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ResourceQuery
{
    protected string $modelClass;
    protected Builder $queryBuilder;
    protected ?\Closure $queryHandler;

    public function __construct(string $modelClass)
    {
        $this->setModel($modelClass);
    }

    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    public function getQueryBuilder(): Builder
    {
        return $this->queryBuilder;
    }

    public function setModel(Model|string $modelClass): void
    {
        $this->modelClass = $modelClass;
        $this->queryBuilder = $modelClass::query();
    }

    public function findRecord($id)
    {
        $queryBuilder = $this->queryBuilder;

        $mainTable = $queryBuilder->getModel()->getTable();
        $queryBuilder->addSelect($mainTable.'.*');
        $key = $mainTable.'.'.$queryBuilder->getModel()->getKeyName();

        return $queryBuilder->where($key, $id)->first();
    }

    public function createRecordInstance(): Model
    {
        return new $this->modelClass();
    }
}
