<?php

use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    use \App\Traits\EnumChangeTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->setEnumValues(
            'payment_transactions',
            'status',
            [
                'pending',
                'success',
                'fail',
                'cancel',
                'expiry',
            ],
            false,
            'pending'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->setEnumValues(
            'payment_transactions',
            'status',
            [
                'pending',
                'failed',
                'paid',
            ],
            false,
            'pending'
        );
    }
};
