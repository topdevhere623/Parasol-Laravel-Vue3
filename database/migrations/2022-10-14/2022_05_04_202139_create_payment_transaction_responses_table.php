<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTransactionResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transaction_responses', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_transaction_id')->index();
            $table->json('response_json');
            $table->foreign('payment_transaction_id')
                ->references('id')
                ->on('payment_transactions');
        });

        \App\Models\Payments\PaymentTransaction::chunkById(100, function ($items) {
            $items->each(function ($item) {
                if ($item->response_json) {
                    \App\Models\Payments\PaymentTransactionResponse::insert([
                        'payment_transaction_id' => $item->id,
                        'response_json' => $item->response_json,
                    ]);
                }
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_transaction_responses');
    }
}
