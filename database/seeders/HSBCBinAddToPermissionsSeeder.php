<?php

namespace Database\Seeders;

use App\Models\HSBCBin;
use App\Models\Laratrust\Permission;
use Illuminate\Database\Seeder;
use ParasolCRM\Activities\Facades\Activity;

class HSBCBinAddToPermissionsSeeder extends Seeder
{
    public function run()
    {
        Activity::disable();

        $data = [
            'model' => HSBCBin::class,
            'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
            'display_name' => 'HSBC Bins',
        ];

        foreach ($data['routes'] as $route) {
            Permission::updateOrCreate([
                'name' => $route.'-'.$data['model'],
                'display_name' => $data['display_name'],
            ]);
        }
    }
}
