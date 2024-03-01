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
        \App\Models\Payments\PaymentMethod::create(
            [
                'title' => 'Paytabs HSBC cards',
                'website_title' => 'HSBC Paytabs',
                'send_email_invoice' => 1,
                'code' => 'paytabs_hsbc',
                'zoho_chartofaccount_id' => '2296850000000094455',
            ]
        );
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
