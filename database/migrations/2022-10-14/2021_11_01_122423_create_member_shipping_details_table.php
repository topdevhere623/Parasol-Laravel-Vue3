<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberShippingDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('member_shipping_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('old_id')
                ->nullable();
            $table->unsignedBigInteger('member_id');
            $table->string('first_name')
                ->nullable();
            $table->string('last_name')
                ->nullable();
            $table->string('company_name')
                ->nullable();
            $table->unsignedBigInteger('country_id')
                ->nullable();
            $table->string('city')
                ->nullable();
            $table->string('state')
                ->nullable();
            $table->string('street')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('member_id')
                ->references('id')
                ->on('members');

            $table->foreign('country_id')
                ->references('id')
                ->on('countries');
        });
    }

    public function down()
    {
        Schema::dropIfExists('member_shipping_details');
    }
}
