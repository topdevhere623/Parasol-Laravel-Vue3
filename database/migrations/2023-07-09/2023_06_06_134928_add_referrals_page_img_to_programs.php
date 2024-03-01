<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table
                ->string('referrals_page_img')
                ->after('referrals_page')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('referrals_page_img');
        });
    }
};
