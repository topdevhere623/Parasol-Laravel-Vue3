<?php

namespace Database\Seeders;

use App\Models\BackofficeUser;
use App\Models\Laratrust\Team;
use Illuminate\Database\Seeder;

class BackofficeUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = BackofficeUser::updateOrCreate(
            [
                'email' => 'admin@admin.com',
            ],
            [
                'first_name' => 'John Doe',
                'password' => bcrypt('111'),
                'created_at' => time(),
            ]
        );

        $user->attachRole('supervisor', Team::TEAM_IDS['adv_management']);
    }
}
