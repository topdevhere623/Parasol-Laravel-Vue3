<?php

use App\Models\Laratrust\Permission;
use App\Models\Laratrust\Role;

if (!function_exists('seed_permissions')) {
    function seed_permissions(
        $model,
        $displayName,
        $roles = ['supervisor', 'manager'],
        $routes = ['index', 'create', 'view', 'update', 'delete', 'log', 'export']
    ): void {
        $data = [
            'model' => $model,
            'display_name' => $displayName,
            'routes' => $routes,
        ];

        foreach ($data['routes'] as $route) {
            $permission = Permission::updateOrCreate([
                'name' => $route.'-'.$data['model'],
                'display_name' => $data['display_name'],
            ]);

            foreach ($roles as $role) {
                Role::whereName($role)->first()->attachPermission($permission);
            }
        }
    }
}
