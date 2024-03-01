<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_mamo_links', function (Blueprint $table) {
            $table->id();
            $table->uuid()
                ->index();
            $table->string('mamo_id', 25)
                ->index()
                ->nullable();
            $table->string('link', 100)
                ->nullable();
            $table->foreignId('payment_id')
                ->index()
                ->nullable();
            $table->boolean('is_active')
                ->default(true);
            $table->text('response')
                ->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        \App\Models\Payments\PaymentMethod::create(
            [
                'title' => 'Mamopay monthly card charges',
                'website_title' => 'Monthly card charges',
                'send_email_invoice' => 1,
                'code' => 'mamo_monthly',
                'zoho_chartofaccount_id' => '2296850000003171655',
            ]
        );

        \App\Models\Payments\PaymentMethod::find(17)->update(
            ['code' => 'mamo', 'zoho_chartofaccount_id' => '2296850000003171655']
        );

        Schema::table('member_payment_schedules', function (Blueprint $table) {
            $table->date('card_expiry_date')
                ->nullable()
                ->change();
            $table->string('card_token', 255)
                ->nullable()
                ->change();
            $table->foreignId('payment_method_id')
                ->nullable()
                ->after('first_number_of_days')
                ->index();
        });

        DB::table('member_payment_schedules')
            ->update(['payment_method_id' => 5]);
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
