<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCheckinBookinPolicyParkingDescriptionsToClubs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->after('what_members_love', function ($table) {
                $table->text('check_in_area');
                $table->text('booking_policy_for_activities');
                $table->text('parking');
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
        Schema::table('clubs', function (Blueprint $table) {
            $table->removeColumn('check_in_area');
            $table->removeColumn('booking_policy_for_activities');
            $table->removeColumn('parking');
        });
    }
}
