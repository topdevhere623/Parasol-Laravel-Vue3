<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePassportLoginHistories extends Migration
{
    public function up()
    {
        Schema::create('passport_login_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('user_type');
            $table->string('token_id', 100);
            $table->ipAddress('ip_address')
                ->nullable();
            $table->string('user_agent')
                ->nullable();

            $table->timestamp('created_at');

            $table->index(['user_id', 'user_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('passport_login_histories');
    }
}
