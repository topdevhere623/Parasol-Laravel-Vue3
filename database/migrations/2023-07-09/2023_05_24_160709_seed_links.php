<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up()
    {
        Setting::create([
            'key' => 'links',
            'value' => json_encode([
                [
                    'title' => 'The Entertainer offer',
                    'url' => '',
                ],
                [
                    'title' => 'Check our website',
                    'url' => '',
                ],
                [
                    'title' => 'WhatsApp us',
                    'url' => '',
                ],
                [
                    'title' => 'Follow us on Instagram',
                    'url' => '',
                ],
                [
                    'title' => 'Get a detailed club guide',
                    'url' => '/clubs',
                ],
                [
                    'title' => 'Review FAQs',
                    'url' => '/faq',
                ],
            ]),
            'editable' => 1,
            'value_type' => 'array',
        ]);
    }

    public function down()
    {
    }
};
