<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up()
    {
        Setting::create([
            'key' => 'company_details',
            'value' => '{"title":"Parasol Loyalty Card Services LLC","address":"Office 1405, Latifa Tower<br>Sheikh Zayed Road, Dubai, UAE","phone":"+971 4 568 2083","email":"memberships@advplus.ae"}',
            'editable' => 0,
            'value_type' => 'array',
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
