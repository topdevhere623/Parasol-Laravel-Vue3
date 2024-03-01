<?php

use Illuminate\Database\Migrations\Migration;

class SeedHsbcData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Artisan::call('db:seed', [
            '--class' => \Database\Seeders\HSBCSeeder::class,
        ]);
        Artisan::call('db:seed', [
            '--class' => \Database\Seeders\HSBCBinAddToPermissionsSeeder::class,
        ]);
        Artisan::call('db:seed', [
            '--class' => \Database\Seeders\HsbcUsedCardAddToPermissionsSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
