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
            'checkins',
            'status',
            [
                'checked_in',
                'checked_out',
                'paid_guest_fee',
                'turned_away',
                'turned_away_expired',
            ],
            false,
            'checked_in'
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
            'checkins',
            'status',
            [
                'checked_in',
                'checked_out',
                'paid_guest_fee',
                'turned_away',
            ],
            false,
            'checked_in'
        );
    }
};
