<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use ParasolCRM\Activities\Facades\Activity;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Activity::disable();

        $this->call(
            [
                TeamsSeeder::class,
                RolesSeeder::class,
                PermissionsSeeder::class,
                BackofficeUsersTableSeeder::class,

                SystemsSeeder::class,
                CitiesSeeder::class,
                HSBCSeeder::class,
            ]
        );
    }
}
