<?php

namespace ParasolCRM;

trait EmptyScheme
{
    public function fields(): array
    {
        return [];
    }

    public function actions(): array
    {
        return [];
    }

    public function layout(): array
    {
        return [];
    }

    public function statuses(): array
    {
        return [];
    }

    public function filters(): array
    {
        return [];
    }

    public function charts(): array
    {
        return [];
    }

    public function layoutFilters(): array
    {
        return [];
    }
}
