<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberMembershipDurationTable extends Migration
{
    public function up()
    {
        Schema::create('member_membership_duration', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('membership_duration_id');

            $table->foreign('member_id')
                ->references('id')
                ->on('members');
            $table->foreign('membership_duration_id')
                ->references('id')
                ->on('membership_durations');
        });
    }

    public function down()
    {
        Schema::dropIfExists('member_membership_duration');
    }
}
