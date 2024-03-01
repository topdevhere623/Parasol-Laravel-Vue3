<?php

namespace ParasolCRM;

class ResourceScheme
{
    use EmptyScheme;

    // TODO: Remove
    protected function isAdmin(): bool
    {
        if ($user = \Auth::guard('backoffice_user')->user()) {
            return $user->hasRole('supervisor');
        }
        // TODO: По непонятным причинам иногда $user = null
        return true;
    }

    public function getModel()
    {
        return static::$model;
    }

    public function getDefaultSort()
    {
        return [
            'defaultSortBy' => property_exists(static::class, 'defaultSortBy') ? static::$defaultSortBy : '',
            'defaultSortDirection' => property_exists(
                static::class,
                'defaultSortDirection'
            ) ? static::$defaultSortDirection : '',
        ];
    }
}
