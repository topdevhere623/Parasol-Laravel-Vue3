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
        Schema::table('plans', function (Blueprint $table) {
            $table->foreignId('membership_duration_id')
                ->after('membership_type_id')
                ->index();
            $table->boolean('is_family_plan_available')
                ->default(true)
                ->after('renewal_email_type');
        });

        \DB::table('plans')->where('duration', 1)->where('duration_type', 'month')->update(
            ['is_family_plan_available' => false]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropForeign('plans_membership_duration_id_foreign');
            $table->dropColumn('membership_duration_id');
            $table->dropColumn('is_family_plan_available');
        });
    }
};
