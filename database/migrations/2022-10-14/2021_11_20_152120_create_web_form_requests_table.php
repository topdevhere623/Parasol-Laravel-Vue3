<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebFormRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('web_form_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->enum('status', [
                'new_request',
                'pending',
                'respond',
            ])->default('new_request');
            $table->string('name');
            $table->string('email')
                ->nullable();
            $table->string('phone')
                ->nullable();
            $table->json('data')
                ->nullable();
            $table->text('note')
                ->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('web_form_requests');
    }
}
