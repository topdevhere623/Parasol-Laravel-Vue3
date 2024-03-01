<?php

namespace Database\Seeders;

use App\Models\System;
use Illuminate\Database\Seeder;

class SystemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        System::firstOrCreate([
            'first_name' => 'advplus',
            'last_name' => 'SYSTEM',
            'email' => 'robot@advplus.com',
        ]);
    }
}
