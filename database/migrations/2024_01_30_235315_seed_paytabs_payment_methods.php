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
                'title' => 'Paytabs monthly card charges',
                'website_title' => 'Monthly card charges',
                'send_email_invoice' => 1,
                'code' => 'paytabs_monthly',
                'zoho_chartofaccount_id' => '2296850000000094455',
                'status' => \App\Models\Payments\PaymentMethod::STATUSES['inactive'],
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
