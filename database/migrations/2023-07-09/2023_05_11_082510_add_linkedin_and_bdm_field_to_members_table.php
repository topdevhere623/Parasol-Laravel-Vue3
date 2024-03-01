<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->after('area_id', function (Blueprint $table) {
                $table->boolean('linkedin_verified')
                    ->default(0);
                $table->string('linkedin_url')
                    ->nullable();
                $table->foreignId('bdm_backoffice_user_id')
                    ->nullable()
                    ->comment('Business Development Manager')
                    ->index()
                    ->constrained('backoffice_users');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'linkedin_verified',
                'linkedin_url',
                'bdm_backoffice_user_id',
            ]);
        });
    }
};
