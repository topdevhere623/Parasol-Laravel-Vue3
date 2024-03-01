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
        Schema::table('referrals', function (Blueprint $table) {
            $table
                ->enum('reward', [
                    'cashback',
                    'additional_month',
                    'additional_club',
                ])
                ->after('member_no')
                ->nullable()
                ->default(null);

            $table
                ->enum('reward_status', [
                    'not_selected',
                    'pending',
                    'complete',
                ])
                ->after('reward')
                ->default('not_selected');

            $table->text('notes')
                ->after('reward_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropColumn('reward');
            $table->dropColumn('reward_status');
            $table->dropColumn('notes');
        });
    }
};
