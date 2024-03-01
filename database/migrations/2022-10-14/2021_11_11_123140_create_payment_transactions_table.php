<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('remote_id', 70);
            $table->enum('status', [
                'pending',
                'failed',
                'paid',
            ])->default('pending')
                ->index();
            $table->unsignedBigInteger('payment_id')
                ->nullable()
                ->index();
            $table->unsignedBigInteger('payment_method_id')
                ->nullable()
                ->index();
            $table->double('amount');
            $table->json('response_json')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('payment_id')
                ->references('id')
                ->on('payments');

            $table->foreign('payment_method_id')
                ->references('id')
                ->on('payment_methods');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_transactions');
    }
}
