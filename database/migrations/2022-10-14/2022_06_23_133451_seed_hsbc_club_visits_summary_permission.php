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
            'model' => \App\Models\Reports\HSBCClubVisitsSummary::class,
            'routes' => ['index', 'view'],
            'display_name' => 'HSBC Club Visits',
        ];

        foreach ($data['routes'] as $route) {
            $permission = \App\Models\Laratrust\Permission::updateOrCreate([
                'name' => $route.'-'.$data['model'],
                'display_name' => $data['display_name'],
            ]);

            \App\Models\Laratrust\Role::whereIn('name', [
                'supervisor',
                'manager',
                'hsbc_manager',
                'hsbc_report',
            ])->each(fn ($item) => $item->attachPermission($permission));
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
