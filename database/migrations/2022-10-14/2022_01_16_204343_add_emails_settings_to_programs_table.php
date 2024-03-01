<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailsSettingsToProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->after('color', function (Blueprint $table) {
                $table->boolean('send_booking_welcome_email')
                    ->default(true);
                $table->boolean('send_booking_invoice_email')
                    ->default(true);
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('send_booking_welcome_email', 'send_booking_welcome_email');
        });
    }
}
