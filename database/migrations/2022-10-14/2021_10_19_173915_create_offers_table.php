<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffersTable extends Migration
{
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('offer_type_id');
            $table->string('logo')
                ->nullable();
            $table->string('offer_value')
                ->nullable();
            $table->text('about')
                ->nullable();
            $table->text('terms')
                ->nullable();
            $table->string('location')
                ->nullable();
            $table->string('area')
                ->nullable();
            $table->string('emirate')
                ->nullable();
            $table->string('website')
                ->nullable();
            $table->string('map')
                ->nullable();
            $table->string('offer_code')
                ->nullable();
            $table->string('online_shop_link')
                ->nullable();
            $table->date('offer_expiry')
                ->nullable();
            $table->enum('status', [
                'active',
                'inactive',
            ])
                ->default('active');
            $table->integer('sort')
                ->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'name',
                'offer_type_id',
                'emirate',
                'status',
            ]);

            $table->foreign('offer_type_id')
                ->references('id')
                ->on('offer_types');
        });
    }

    public function down()
    {
        Schema::dropIfExists('offers');
    }
}
