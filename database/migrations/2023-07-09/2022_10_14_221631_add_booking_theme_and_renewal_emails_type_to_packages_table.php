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
        Schema::table('packages', function (Blueprint $table) {
            $table->after('membership_source_id', function (Blueprint $table) {
                $table->boolean('show_header')
                    ->default(true);
                $table->boolean('show_header_menu')
                    ->default(true);
                $table->boolean('show_footer')
                    ->default(true);
                $table->boolean('show_tawk_chat')
                    ->default(true);
                $table->boolean('show_coupons')
                    ->default(true);
                $table->boolean('show_clubs')
                    ->default(true);
                $table->boolean('show_steps_progress')
                    ->default(true);
                $table->string('apply_coupon', 100)
                    ->nullable();
            });
        });

        \App\Models\Program::where('source', \App\Models\Program::SOURCE_MAP['hsbc'])->first()
            ->packages->each(function (App\Models\Package $package) {
                $package->apply_coupon = 'z52XIdjBAs';
                $package->show_coupons = false;
                $package->show_header = false;
                $package->show_footer = false;
                $package->show_tawk_chat = false;
                $package->show_clubs = false;
                $package->show_steps_progress = false;

                $package->save();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'show_header',
                'show_header_menu',
                'show_footer',
                'show_tawk_chat',
                'show_coupons',
                'show_clubs',
                'show_steps_progress',
                'apply_coupon',
            ]);
        });
    }
};
