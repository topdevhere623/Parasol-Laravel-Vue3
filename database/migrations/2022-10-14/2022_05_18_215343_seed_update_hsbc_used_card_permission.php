<?php

use Illuminate\Database\Migrations\Migration;

class SeedUpdateHsbcUsedCardPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = [
            'model' => \App\Models\HSBCUsedCard::class,
            'routes' => ['update'],
            'display_name' => 'HSBC Used Cards',
        ];

        foreach ($data['routes'] as $route) {
            $permission = \App\Models\Laratrust\Permission::updateOrCreate([
                'name' => $route.'-'.$data['model'],
                'display_name' => $data['display_name'],
            ]);

            \App\Models\Laratrust\Role::whereName('supervisor')->first()->attachPermission($permission);
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
}
