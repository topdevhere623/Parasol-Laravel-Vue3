<?php

use App\Traits\EnumChangeTrait;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    use EnumChangeTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->setEnumValues(
            'member_payment_schedules',
            'status',
            [
                'active',
                'inactive',
                'stopped',
                'failed',
                'completed',
            ],
            false,
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
            'member_payment_schedules',
            'status',
            [
                'active',
                'inactive',
                'stopped',
                'failed',
            ],
            false,
        );
    }
};
