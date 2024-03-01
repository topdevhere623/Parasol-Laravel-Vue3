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
            $table->string('website_title', 100)
                ->after('title');
            $table->boolean('send_email_invoice')
                ->default(true)
                ->after('website_title');
            $table->string('code', 50)
                ->change()
                ->index();
        });

        \App\Models\Payments\PaymentMethod::each(function (App\Models\Payments\PaymentMethod $item) {
            if ($item->id == 2) {
                $item->website_title = 'Credit / Debit Card';
            } else {
                $item->website_title = $item->title;
            }

            $item->send_email_invoice = !in_array($item->code, ['bank_transfer', 'cash']);

            Activity::disable();
            $item->save();
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
            $table->removeColumn('website_title');
            $table->string('code', 255)
                ->change();
        });
    }
};
