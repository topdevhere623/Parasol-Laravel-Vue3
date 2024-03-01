<?php

use App\Models\Payments\PaymentTransaction;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentTransactionsPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        seed_permissions(PaymentTransaction::class, 'Payment Transactions', ['supervisor']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
