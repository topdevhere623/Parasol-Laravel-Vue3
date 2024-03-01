<?php

namespace App\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasManyWithSecondKey extends HasMany
{
    public $secondLocalKey;

    public function __construct(Builder $query, Model $parent, string $foreignKey, string $localKey, string $secondLocalKey)
    {
        $this->secondLocalKey = $secondLocalKey;
        parent::__construct($query, $parent, $foreignKey, $localKey);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            $query = $this->getRelationQuery();

            $query->whereIn($this->foreignKey, [$this->getParentKey(), $this->getParentSecondKey()]);

            $query->whereNotNull($this->foreignKey);
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $whereIn = $this->whereInMethod($this->parent, $this->localKey);

        $keys = collect($models)->map(function ($value) {
            return $value->{$this->secondLocalKey} ?? $value->{$this->localKey};
        })->values()->unique(null, true)->sort()->all();

        $this->getRelationQuery()->{$whereIn}(
            $this->foreignKey,
            $keys
        );
    }

    /**
     * Match the eagerly loaded results to their many parents.
     *
     * @param  array  $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @param  string  $type
     * @return array
     */
    protected function matchOneOrMany(array $models, Collection $results, $relation, $type)
    {
        $dictionary = $this->buildDictionary($results);

        // Once we have the dictionary we can simply spin through the parent models to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work. Then we'll just return them.
        foreach ($models as $model) {
            if (isset($dictionary[$key = $this->getDictionaryKey($model->getAttribute($this->secondLocalKey) ?? $model->getAttribute($this->localKey))])) {
                $model->setRelation(
                    $relation,
                    $this->getRelationValue($dictionary, $key, $type)
                );
            }
        }

        return $models;
    }

    /**
     * Get the key value of the parent's local key.
     *
     * @return mixed
     */
    public function getParentSecondKey()
    {
        return $this->parent->getAttribute($this->secondLocalKey);
    }
}
