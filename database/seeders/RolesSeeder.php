<?php

namespace Database\Seeders;

use App\Models\Laratrust\Role;
use App\Models\Laratrust\Team;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $advManagementTeam = Team::where('name', Team::TEAM_IDS['adv_management'])->first();

        Role::updateOrCreate([
            'name' => 'supervisor',
            'display_name' => 'Supervisor',
        ])->teams()->sync($advManagementTeam);

        Role::updateOrCreate([
            'name' => 'manager',
            'display_name' => 'Manager',
        ])->teams()->sync($advManagementTeam);

        Role::updateOrCreate([
            'name' => 'club_manager',
            'display_name' => 'Club Manager',
        ])->teams()->sync(Team::where('name', Team::TEAM_IDS['club_admins'])->first());
    }
}
