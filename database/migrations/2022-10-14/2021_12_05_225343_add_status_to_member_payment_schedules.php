<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToMemberPaymentSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_payment_schedules', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'stopped', 'failed'])
                ->index()
                ->after('id');
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
            $table->removeColumn('status');
        });
    }
}
