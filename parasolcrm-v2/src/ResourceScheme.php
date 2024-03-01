<?php

namespace ParasolCRMV2;

use Illuminate\Support\Str;
use ParasolCRMV2\Contracts\Menu\MenuFiller;

abstract class ResourceScheme implements MenuFiller
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

    public static function url(): ?string
    {
        return str(class_basename(static::class))->replace('Resource', '')->pluralStudly()->kebab()->value();
    }

    public static function singularLabel(): string
    {
        return Str::singular(
            Str::title(Str::snake(class_basename(static::$model), ' '))
        );
    }

    public static function label(): string
    {
        return Str::plural(
            Str::title(Str::snake(class_basename(static::$model), ' '))
        );
    }
}
