<?php

namespace ParasolCRMV2\Builders;

use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Builders\Interfaces\ComponentBuilder;
use ParasolCRMV2\FieldCollection;
use ParasolCRMV2\Fields\RelationField;
use ParasolCRMV2\Makeable;
use ParasolCRMV2\ResourceQuery;

abstract class BaseBuilder implements ComponentBuilder
{
    use Makeable;

    protected ?FieldCollection $fieldCollection = null;
    protected ?Filter $filter = null;
    protected ResourceQuery $resourceQuery;
    protected array $params = [];

    public function __construct(ResourceQuery $resourceQuery, FieldCollection $fieldCollection)
    {
        $this->setFieldCollection($fieldCollection);
        $this->setResourceQuery($resourceQuery);
    }

    public function getResourceQuery(): ?ResourceQuery
    {
        return $this->resourceQuery;
    }

    public function setResourceQuery(?ResourceQuery $resourceQuery): self
    {
        $this->resourceQuery = $resourceQuery;
        return $this;
    }

    public function getFieldCollection(): ?FieldCollection
    {
        return $this->fieldCollection;
    }

    public function setFieldCollection(?FieldCollection $fieldCollection): self
    {
        $this->fieldCollection = $fieldCollection;
        return $this;
    }

    public function setFilter(?Filter $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    public function makeRelation(RelationField $field, Builder $queryBuilder)
    {
        throw_unless(
            !method_exists($queryBuilder, 'joinRelationship'),
            \Exception::class,
            'PowerJoins trait in main model is not on used'
        );

        $relation = $field->getRelationName();

        $queryBuilder->joinRelationship(
            $relation,
            [$queryBuilder->getModel()->{$relation}()->getRelated()->getTable() => fn ($join) => $join->as($relation)],
            'leftJoin',
        );
    }

    public function getLabel(string $key): string
    {
        $model = $this->getResourceQuery()->getModelClass();
        if ($model && method_exists($model, 'getLabel')) {
            $label = $model->getLabel($key);
        }
        return $label ?? str_replace('_', ' ', trim(ucfirst($key)));
    }

    public function label(): string
    {
        return \PrslV2::getResourceLabel();
    }

    public function singularLabel(): string
    {
        return \PrslV2::getResourceSingularLabel();
    }
}
