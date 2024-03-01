<?php

namespace Database\Seeders;

use App\Models\Laratrust\Permission;
use App\Models\Laratrust\Role;
use App\Models\Laratrust\Team;
use App\Models\Reports\HSBCReport;
use Illuminate\Database\Seeder;
use ParasolCRM\Activities\Facades\Activity;

class HSBCReportSeeder extends Seeder
{
    public function run()
    {
        Activity::disable();

        $team = Team::firstOrCreate([
            'name' => 'program_admins',
            'display_name' => 'Program Admins',
        ]);

        $HSBCReportRole = Role::firstOrCreate([
            'name' => 'hsbc_report',
            'display_name' => 'HSBC Report',
        ]);

        $HSBCReportRole->teams()->sync($team);

        $data = [
            'model' => HSBCReport::class,
            'routes' => ['index', 'view', 'log', 'update'],
            'display_name' => 'HSBC Report',
        ];

        foreach ($data['routes'] as $route) {
            Permission::updateOrCreate([
                'name' => $route.'-'.$data['model'],
                'display_name' => $data['display_name'],
            ]);
        }

        $supervisorRole = Role::whereName('supervisor')->first();

        $permissions = Permission::whereDisplayName($data['display_name'])->get();

        $supervisorRole->permissions()->detach($permissions);
        $supervisorRole->permissions()->attach($permissions);

        $HSBCReportRole->permissions()->sync($permissions->where('name', 'index-'.$data['model']));
    }
}
