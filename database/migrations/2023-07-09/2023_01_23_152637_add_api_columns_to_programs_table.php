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
        Schema::table('programs', function (Blueprint $table) {
            $table->after('source', function (Blueprint $table) {
                $table->boolean('has_access_api')
                    ->default(0)
                    ->index();
                $table->string('api_key')
                    ->nullable()
                    ->index();
                $table->foreignId('landing_page_plan_id')
                    ->nullable()
                    ->index()
                    ->constrained('plans');

                $table->foreignId('api_default_package_id')
                    ->nullable()
                    ->index()
                    ->constrained('packages');

                $table->string('webhook_url')
                    ->nullable();
            });
        });

        \App\Models\Program::find(1)->update(['has_access_api' => true]);

        // You Rewards
        \App\Models\Program::find(35)->update([
            'api_key' => '0V3cmUkR4HBKct2Z1yV3OeMWd4ygwbTDqsLozKkTc3nV7M0XfkpB707xNX8pm1eXyJ4iFpoPuYT8cCbQKlAkKbYYUL3dxtuTdVbhd9ULjlyRpFnjT9SWo9Fon1Mh0BnAoWP7lPc7NFKp11fKGwc7jI',
            'has_access_api' => true,
            'api_default_package_id' => 82,
            'landing_page_plan_id' => 263,
            'webhook_url' => 'https://staging.yourrewards.io/advplus/webhook',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            //            $table->dropColumn('api_key');
            $table->dropColumn('landing_page_plan_id', 'api_default_package_id', 'has_access_api');
        });
    }
};
