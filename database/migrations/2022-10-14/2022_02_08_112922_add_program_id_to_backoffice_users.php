<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProgramIdToBackofficeUsers extends Migration
{
    public function up()
    {
        Schema::table('backoffice_users', function (Blueprint $table) {
            $table->unsignedBigInteger('program_id')
                ->after('club_id')
                ->nullable()
                ->index();
        });

        Artisan::call('db:seed', [
            '--class' => \Database\Seeders\HSBCReportSeeder::class,
        ]);
    }

    public function down()
    {
        Schema::table('backoffice_users', function (Blueprint $table) {
            $table->dropColumn('program_id');
        });
    }
}
