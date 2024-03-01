<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBackofficeUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('backoffice_users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')
                ->nullable();
            $table->string('email')
                ->unique();
            $table->string('password');
            $table->enum('status', [
                'active',
                'inactive',
            ])->index()
                ->default('active');
            $table->string('avatar')
                ->nullable();
            $table->rememberToken();

            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->unsignedBigInteger('club_id')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('backoffice_users');
    }
}
