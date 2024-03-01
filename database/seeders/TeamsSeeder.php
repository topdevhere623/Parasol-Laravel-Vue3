<?php

namespace Database\Seeders;

use App\Models\Laratrust\Team;
use Illuminate\Database\Seeder;

class TeamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Team::firstOrCreate(['name' => 'adv_management', 'display_name' => 'ADV Management']);
        Team::firstOrCreate(['name' => 'club_admins', 'display_name' => 'Club Admins']);
    }
}
