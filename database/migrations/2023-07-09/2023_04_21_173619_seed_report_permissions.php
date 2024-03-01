<?php

use App\Models\Laratrust\Permission;
use App\Models\Laratrust\Role;
use App\Models\Reports\ReportClubsByMemberSelection;
use App\Models\Reports\ReportClubsByUsage;
use App\Models\Reports\ReportTopMember;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up()
    {
        $data = [
            [
                'model' => ReportClubsByMemberSelection::class,
                'routes' => ['index'],
                'display_name' => 'Clubs by Member selection',
            ],
            [
                'model' => ReportClubsByUsage::class,
                'routes' => ['index'],
                'display_name' => 'Clubs by Usage',
            ],
            [
                'model' => ReportTopMember::class,
                'routes' => ['index'],
                'display_name' => 'Top Members',
            ],
        ];

        foreach ($data as $datum) {
            foreach ($datum['routes'] as $route) {
                $permission = Permission::updateOrCreate([
                    'name' => $route.'-'.$datum['model'],
                    'display_name' => $datum['display_name'],
                ]);

                Role::whereIn('name', [
                    'supervisor',
                    'manager',
                ])->each(fn ($item) => $item->attachPermission($permission));
            }
        }
    }

    public function down()
    {
        //
    }
};
