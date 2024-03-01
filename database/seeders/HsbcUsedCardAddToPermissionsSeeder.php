<?php

namespace Database\Seeders;

use App\Models\HSBCUsedCard;
use App\Models\Laratrust\Permission;
use Illuminate\Database\Seeder;
use ParasolCRM\Activities\Facades\Activity;

class HsbcUsedCardAddToPermissionsSeeder extends Seeder
{
    public function run()
    {
        Activity::disable();

        $data = [
            'model' => HSBCUsedCard::class,
            'routes' => ['index', 'log', 'view', 'delete'],
            'display_name' => 'HSBC Used Cards',
        ];

        foreach ($data['routes'] as $route) {
            Permission::updateOrCreate([
                'name' => $route.'-'.$data['model'],
                'display_name' => $data['display_name'],
            ]);
        }
    }
}
