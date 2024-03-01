<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralsTable extends Migration
{
    public function up()
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->enum('status', [
                'active',
                'declined',
                'joined',
                'contacted',
                'lead',
                'not_responding',
            ])
                ->default('not_responding');
            $table->string('name');
            $table->string('email')
                ->nullable();
            $table->string('mobile')
                ->nullable();
            $table->string('code')
                ->nullable();
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('used_member_id')
                ->nullable();
            $table->string('member_no')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('member_id')
                ->references('id')
                ->on('members');

            $table->foreign('used_member_id')
                ->references('id')
                ->on('members');
        });
    }

    public function down()
    {
        Schema::dropIfExists('referrals');
    }
}
