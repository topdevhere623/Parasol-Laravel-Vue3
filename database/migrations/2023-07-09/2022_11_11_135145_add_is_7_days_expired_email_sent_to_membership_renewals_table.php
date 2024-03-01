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
        Schema::table('membership_renewals', function (Blueprint $table) {
            $table->boolean('is_7_days_expired_email_sent')
                ->after('is_expired_email_sent')
                ->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('membership_renewals', function (Blueprint $table) {
            $table->dropColumn('is_7_days_expired_email_sent');
        });
    }
};
