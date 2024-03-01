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
        Schema::table('member_payment_schedules', function (Blueprint $table) {
            $table->enum('card_status', ['active', 'failed', 'expired'])
                ->after('charge_date')
                ->index();
            $table->string('card_change_auth_token', 128)
                ->nullable()
                ->after('card_scheme');
        });

        \App\Models\Member\MemberPaymentSchedule::each(function (App\Models\Member\MemberPaymentSchedule $item) {
            if ($item->status == 'failed') {
                $item->card_status = 'failed';
                $item->status = 'active';
                $item->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_payment_schedules', function (Blueprint $table) {
            $table->dropColumn('card_status');
            $table->dropColumn('card_change_auth_token');
        });
    }
};
