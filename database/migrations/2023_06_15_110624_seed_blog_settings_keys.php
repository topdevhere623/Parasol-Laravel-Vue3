<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up()
    {
        Setting::create([
            'key' => 'blogs_heading',
            'value' => 'Best of the week',
            'editable' => 1,
            'value_type' => 'string',
        ]);
        Setting::create([
            'key' => 'blogs_meta_title',
            'value' => 'Best of the week',
            'editable' => 1,
            'value_type' => 'string',
        ]);
        Setting::create([
            'key' => 'blogs_meta_description',
            'value' => 'Best of the week',
            'editable' => 1,
            'value_type' => 'string',
        ]);
        Setting::create([
            'key' => 'blogs_banner_link',
            'value' => '/#join',
            'editable' => 1,
            'value_type' => 'string',
        ]);
    }

    public function down()
    {
    }
};
