<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')
                ->unique();
            $table->unsignedBigInteger('package_id')
                ->index();
            $table->string('title')
                ->nullable();
            $table->enum('vat', [
                'exclude',
                'include',
            ])
                ->default('exclude');
            $table->double('price')
                ->default(0);
            $table->string('small_description')
                ->nullable();
            $table->string('question_mark_description')
                ->nullable();
            $table->integer('duration')
                ->nullable();
            $table->integer('number_of_clubs')
                ->nullable();
            $table->integer('number_of_adults')
                ->nullable();
            $table->integer('number_of_children')
                ->nullable();
            $table->integer('number_of_juniors')
                ->nullable();
            $table->boolean('show_children_block')
                ->nullable();
            $table->double('extra_child_price')
                ->nullable();
            $table->double('extra_junior_price')
                ->nullable();
            $table->unsignedBigInteger('membership_type_id')
                ->nullable()
                ->index();
            $table->string('card_text')
                ->nullable();
            $table->boolean('is_coupon_conditional_purchase')
                ->nullable();
            $table->enum('status', [
                'inactive',
                'active',
            ])
                ->default('active');
            $table->integer('sort')
                ->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('package_id')
                ->references('id')
                ->on('packages');

            $table->foreign('membership_type_id')
                ->references('id')
                ->on('membership_types');
        });
    }

    public function down()
    {
        Schema::dropIfExists('plans');
    }
}
