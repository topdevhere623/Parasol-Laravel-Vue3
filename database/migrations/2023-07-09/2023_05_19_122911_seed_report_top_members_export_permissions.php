<?php

use App\Models\Laratrust\Permission;
use App\Models\Laratrust\Role;
use App\Models\Reports\ReportTopMember;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up()
    {
        $permission = Permission::updateOrCreate([
            'name' => 'export-'.ReportTopMember::class,
            'display_name' => 'ReportTopMembers',
        ]);
        Role::whereName('supervisor')->first()->attachPermission($permission);
        Role::whereName('manager')->first()->attachPermission($permission);
    }

    public function down()
    {
        //
    }
};
