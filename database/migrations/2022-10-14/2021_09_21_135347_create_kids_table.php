<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKidsTable extends Migration
{
    public function up()
    {
        Schema::create('kids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('old_id');
            $table->uuid('uuid')
                ->unique();
            $table->unsignedBigInteger('parent_id')
                ->nullable();
            $table->unsignedBigInteger('booking_id')
                ->nullable();
            $table->string('first_name')
                ->nullable();
            $table->string('last_name')
                ->nullable();
            $table->date('dob')
                ->nullable();
            $table->string('member_id')
                ->nullable();
            $table->string('airtable_id')
                ->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')
                ->references('id')
                ->on('members');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kids');
    }
}
