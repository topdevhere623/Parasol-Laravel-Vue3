<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAmazonPaymentMethodToPaymentMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $paymentMethod = new \App\Models\Payments\PaymentMethod();
            $paymentMethod->title = 'Amazon Payfort';
            $paymentMethod->code = 'amazon_payfort';
            $paymentMethod->status = \App\Models\Payments\PaymentMethod::STATUSES['active'];
            $paymentMethod->save();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            //
        });
    }
}
