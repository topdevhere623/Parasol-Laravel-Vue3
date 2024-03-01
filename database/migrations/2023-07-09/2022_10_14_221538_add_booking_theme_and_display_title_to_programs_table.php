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
            $table->after('send_booking_invoice_email', function (Blueprint $table) {
                $table->string('booking_first_main_color', 7)
                    ->nullable();
                $table->string('booking_second_main_color', 7)
                    ->nullable();
                $table->string('booking_headers_color', 7)
                    ->nullable();
                $table->string('booking_second_headers_color', 7)
                    ->nullable();
                $table->string('booking_coupon_button_color', 7)
                    ->nullable();
                $table->string('booking_confirm_button_color', 7)
                    ->nullable();
                $table->string('booking_total_color', 7)
                    ->nullable();
                $table->string('website_logo', 100)
                    ->nullable();
            });

            $table->string('public_name')
                ->after('name');
        });

        $hsbcProgram
            = \App\Models\Program::where('source', \App\Models\Program::SOURCE_MAP['hsbc'])->first();
        $hsbcProgram->booking_first_main_color = '#109c91';
        $hsbcProgram->booking_second_main_color = '#109c91';
        $hsbcProgram->booking_headers_color = '#109c91';
        $hsbcProgram->booking_second_headers_color = '#109c91';
        $hsbcProgram->booking_total_color = '#109c91';
        $hsbcProgram->booking_confirm_button_color = '#ffb603';
        $hsbcProgram->save();

        \DB::table('programs')->update(['public_name' => \DB::raw('name')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn([
                'website_logo',
                'booking_first_main_color',
                'booking_second_main_color',
                'booking_headers_color',
                'booking_second_headers_color',
                'booking_coupon_button_color',
                'booking_confirm_button_color',
                'booking_total_color',
            ]);
        });
    }
};
