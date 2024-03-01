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
            $table->boolean('show_header_member_portal_link')
                ->default(true)
                ->after('show_header_menu');
            $table->after('show_footer', function (Blueprint $table) {
                $table->boolean('show_footer_description')
                    ->default(true);
                $table->boolean('show_footer_navigation')
                    ->default(true);
                $table->boolean('show_footer_socials')
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
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'show_header_member_portal_link',
                'show_footer_description',
                'show_footer_navigation',
                'show_footer_socials',
            ]);
        });
    }
};
