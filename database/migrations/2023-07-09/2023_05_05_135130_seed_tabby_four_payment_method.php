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
        \App\Models\Payments\PaymentMethod::create([
            'title' => 'Tabby - 4 payments',
            'website_title' => '4 interest free (debit + credit) payments with Tabby',
            'send_email_invoice' => true,
            'code' => 'tabby_four',
            'status' => 'active',
        ]);
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
