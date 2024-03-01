<?php

use App\Models\Channel;
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
        $data = [
            'model' => Channel::class,
            'routes' => ['index', 'create', 'view', 'update', 'delete'],
            'display_name' => 'Channels',
        ];
        foreach ($data['routes'] as $route) {
            $permission = Permission::updateOrCreate([
                'name' => $route.'-'.$data['model'],
                'display_name' => $data['display_name'],
            ]);
            Role::whereName('supervisor')->first()->attachPermission($permission);
            Role::whereName('manager')->first()->attachPermission($permission);
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
