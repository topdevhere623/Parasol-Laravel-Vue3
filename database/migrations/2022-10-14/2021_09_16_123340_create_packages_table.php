<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagesTable extends Migration
{
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')
                ->unique();
            $table->unsignedBigInteger('program_id')
                ->index()
                ->nullable();
            $table->string('title')
                ->nullable();
            $table->string('slug')
                ->index()
                ->unique();
            $table->string('price_description')
                ->nullable();
            $table->string('image', 50)
                ->nullable();
            $table->string('mobile_image', 50)
                ->nullable();
            $table->text('description')
                ->nullable();
            $table->enum('status', [
                'inactive',
                'active',
            ])->default('active')
                ->index();

            $table->boolean('show_merit_gift_code_block')
                ->default(0);
            $table->boolean('show_on_homepage')
                ->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('program_id')
                ->references('id')
                ->on('programs');
        });
    }

    public function down()
    {
        Schema::dropIfExists('packages');
    }
}
