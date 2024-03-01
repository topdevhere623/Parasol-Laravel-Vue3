<?php

namespace App\Models\Traits;

use Illuminate\Support\Collection;

trait Selectable
{
    public static function getSelectable(): Collection
    {
        $model = new static();
        $query = $model->query();

        $query->orderBy($model->getSelectableOrderColumn(), $model->getSelectableOrderDirection());

        return $query->pluck($model->getSelectableValue(), $model->getSelectableKey());
    }

    private function getSelectableValue(): ?string
    {
        return property_exists($this, 'selectableValue') ? $this->selectableValue : 'id';
    }

    private function getSelectableKey(): ?string
    {
        return property_exists($this, 'selectableKey') ? $this->selectableKey : 'id';
    }

    public function getSelectableOrderColumn(): ?string
    {
        return property_exists(
            $this,
            'selectableOrderColumn'
        ) ? $this->selectableOrderColumn : $this->getSelectableValue();
    }

    public function getSelectableOrderDirection(): ?string
    {
        return property_exists($this, 'selectableOrderDirection') ? $this->selectableOrderDirection : 'asc';
    }
}
