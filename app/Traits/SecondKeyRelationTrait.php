<?php

namespace App\Traits;

use App\Relations\HasManyWithSecondKey;
use App\Relations\HasOneWithSecondKey;

trait SecondKeyRelationTrait
{
    public function hasOneWithSecondKey(
        string $related,
        ?string $foreignKey = null,
        ?string $localKey = null,
        ?string $secondLocalKey = null
    ): HasOneWithSecondKey {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        $secondLocalKey ??= 'parent_id';

        return new HasOneWithSecondKey(
            $instance->newQuery(),
            $this,
            $instance->getTable().'.'.$foreignKey,
            $localKey,
            $secondLocalKey
        );
    }

    public function hasManyWithSecondKey(
        string $related,
        ?string $foreignKey = null,
        ?string $localKey = null,
        ?string $secondLocalKey = null
    ): HasManyWithSecondKey {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        $secondLocalKey ??= 'parent_id';

        return new HasManyWithSecondKey(
            $instance->newQuery(),
            $this,
            $instance->getTable().'.'.$foreignKey,
            $localKey,
            $secondLocalKey
        );
    }
}
