<?php

use App\Traits\EnumChangeTrait;
use Illuminate\Database\Migrations\Migration;

class UpdateStatusInPaymentTransactions extends Migration
{
    use EnumChangeTrait;

    public function up()
    {
        $this->setEnumValues(
            'payment_transactions',
            'status',
            [
                'pending',
                'failed',
                'paid',
                'authorized',
            ],
            false,
            'pending'
        );
    }

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
}
