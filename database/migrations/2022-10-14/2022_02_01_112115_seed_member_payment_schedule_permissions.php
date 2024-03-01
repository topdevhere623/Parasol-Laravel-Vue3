<?php

use App\Models\Member\MemberPaymentSchedule;
use Illuminate\Database\Migrations\Migration;

class SeedMemberPaymentSchedulePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        seed_permissions(MemberPaymentSchedule::class, 'Member Payment Schedule', ['supervisor']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
