<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Laratrust\Permission;
use App\Models\Laratrust\Role;
use App\Models\Laratrust\Team;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Fields\BelongsToMany;
use ParasolCRMV2\Fields\Permissions;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\ResourceScheme;

class RoleResource extends ResourceScheme
{
    public static $model = Role::class;

    public function fields(): array
    {
        return [
            Text::make('name')
                ->sortable(),
            Text::make('display_name')
                ->rules(['required', 'string', 'max:40'])
                ->sortable(),
            BelongsToMany::make('teams', Team::class, 'teams')
                ->titleField('display_name')
                ->sortable()
                ->rules(['required']),
            Text::make('description')
                ->onlyOnForm(),

            Permissions::make('permissions', Permission::class, 'permissions'),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make()->attach([
                'name',
                'display_name',
                'description',
                'teams',
                'permissions',
            ])->hasAccess(function () {
                return $this->isAdmin();
            }),
        ];
    }
}
