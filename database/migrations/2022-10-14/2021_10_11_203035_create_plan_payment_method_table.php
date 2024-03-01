<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanPaymentMethodTable extends Migration
{
    public function up()
    {
        Schema::create('plan_payment_method', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('payment_method_id');

            $table->foreign('plan_id')
                ->references('id')
                ->on('plans');

            $table->foreign('payment_method_id')
                ->references('id')
                ->on('payment_methods');
        });
    }

    public function down()
    {
        Schema::dropIfExists('plan_payment_method');
    }
}
