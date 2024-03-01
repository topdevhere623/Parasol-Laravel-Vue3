<?php

use App\Models\Area;
use App\Models\City;
use App\Models\Laratrust\Permission;
use App\Models\Laratrust\Role;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (
            [
                [
                    'model' => City::class,
                    'display_name' => 'Cities',
                ],
                [
                    'model' => Area::class,
                    'display_name' => 'Areas',
                ],
            ] as $datum
        ) {
            $model = $datum['model'];
            $displayName = $datum['display_name'];
            foreach (['index', 'create', 'view', 'update', 'delete'] as $route) {
                $permission = Permission::updateOrCreate([
                    'name' => "{$route}-{$model}",
                    'display_name' => $displayName,
                ]);
                Role::whereName('supervisor')->first()->attachPermission($permission);
                Role::whereName('manager')->first()->attachPermission($permission);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
