<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->after('status', fn ($table) => $table->timestamps());
        });

        \App\Models\Payments\PaymentMethod::create([
            'title' => 'Tabby - 3 payments',
            'website_title' => '3 interest free (debit + credit) payments with Tabby',
            'send_email_invoice' => true,
            'code' => 'tabby_three',
            'status' => 'active',
        ]);

        \App\Models\Payments\PaymentMethod::create([
            'title' => 'Tabby - 6 payments',
            'website_title' => '6 interest free (credit cards only) payments with Tabby',
            'send_email_invoice' => true,
            'code' => 'tabby_six',
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
