<?php

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
            'model' => \App\Models\Reports\ProgramReportMember::class,
            'routes' => ['index', 'view', 'export'],
            'display_name' => 'Program Report Members',
        ];

        foreach ($data['routes'] as $route) {
            $permission = \App\Models\Laratrust\Permission::updateOrCreate([
                'name' => $route.'-'.$data['model'],
                'display_name' => $data['display_name'],
            ]);

            \App\Models\Laratrust\Role::whereName('supervisor')->first()->attachPermission($permission);
            \App\Models\Laratrust\Role::whereName('manager')->first()->attachPermission($permission);
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
