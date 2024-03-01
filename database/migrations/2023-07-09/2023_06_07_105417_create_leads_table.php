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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->uuid()
                ->index();
            $table->string('first_name', 70)
                ->nullable();
            $table->string('last_name', 70)
                ->nullable();
            $table->string('email', 50)
                ->nullable()
                ->index();
            $table->string('phone', 30)->nullable();
            $table->foreignId('backoffice_user_id')
                ->nullable()
                ->index();
            $table->unsignedBigInteger('nocrm_id')
                ->nullable()
                ->index();
            $table->unsignedBigInteger('nocrm_owner_id')
                ->nullable()
                ->index();

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
        Schema::dropIfExists('leads');
    }
};
