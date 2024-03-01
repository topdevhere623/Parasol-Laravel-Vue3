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
        Schema::create('program_api_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid()
                ->index();
            $table->string('external_id', 100)
                ->index();
            $table->foreignId('member_id')
                ->nullable()
                ->index()
                ->constrained();
            $table->foreignId('program_id')
                ->index()
                ->constrained();
            $table->foreignId('booking_id')
                ->nullable()
                ->index()
                ->constrained();
            $table->boolean('booking_webhook_sent')
                ->default(0);
            $table->json('request')
                ->nullable();
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
        Schema::dropIfExists('program_api_requests');
    }
};
