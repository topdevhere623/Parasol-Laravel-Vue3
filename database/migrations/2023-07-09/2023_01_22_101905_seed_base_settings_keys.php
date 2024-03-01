<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Setting::create([
            'key' => 'hsbc_free_checkout_package_id',
            'value' => 28,
            'editable' => 1,
            'value_type' => 'int',
        ]);

        Setting::create([
            'key' => 'hsbc_paid_checkout_package_id',
            'value' => 29,
            'editable' => 1,
            'value_type' => 'int',
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
