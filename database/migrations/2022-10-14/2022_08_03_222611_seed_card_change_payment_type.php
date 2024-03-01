<?php

use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $paymentType = \App\Models\Payments\PaymentType::make();
        $paymentType->title = 'Payment card change';
        $paymentType->is_deletable = false;
        $paymentType->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
