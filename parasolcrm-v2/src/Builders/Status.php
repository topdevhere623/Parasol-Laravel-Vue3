<?php

declare(ticks=1);

namespace ParasolCRMV2\Builders;

use ParasolCRMV2\FieldCollection;
use ParasolCRMV2\ResourceQuery;

class Status extends BaseBuilder
{
    private array $statuses = [];

    public function __construct(ResourceQuery $resourceQuery, FieldCollection $fieldCollection, array $statuses)
    {
        parent::__construct($resourceQuery, $fieldCollection);

        $this->statuses = $statuses;
    }

    public function build(): array
    {
        $statuses = [];

        $queryBuilder = $this->getResourceQuery()->getQueryBuilder();

        foreach ($this->fieldCollection->getRelationFields() as $relationField) {
            $this->makeRelation($relationField, $queryBuilder);
        }

        if ($this->filter && count($this->filter->getFilters())) {
            $this->filter->applyFilters($queryBuilder);
        }

        foreach ($this->getStatuses() as $status) {
            $statuses[] = $status->resolveData($queryBuilder->clone());
        }

        return $statuses;
    }

    public function getStatuses()
    {
        return array_filter(
            $this->statuses,
            function ($status) {
                return $status->checkHasAccess();
            }
        );
    }
}
