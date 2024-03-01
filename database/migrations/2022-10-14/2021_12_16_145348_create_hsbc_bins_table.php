<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHSBCBinsTable extends Migration
{
    public function up()
    {
        Schema::create('hsbc_bins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('status', [
                'active',
                'inactive',
            ])
                ->default('active');
            $table->enum('type', [
                'credit', 'debit', 'test',
            ])->default('credit');
            $table->string('title', 70);
            $table->mediumInteger('bin')
                ->unsigned()
                ->index();
            $table->boolean('free_checkout')
                ->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hsbc_bins');
    }
}
