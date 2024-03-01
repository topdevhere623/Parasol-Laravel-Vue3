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
        Schema::table('leads', function (Blueprint $table) {
            $table->after('crm_step_id', function (Blueprint $table) {
                $table->date('remind_date')->nullable();
                $table->time('remind_time')->nullable();
                $table->timestamp('reminder_at')->nullable();
                $table->unsignedBigInteger('reminder_duration')->nullable();
                $table->unsignedBigInteger('reminder_activity_id')->nullable();
                $table->unsignedBigInteger('reminder_activity_log_id')->nullable();
                $table->string('reminder_note')->nullable();
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
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('remind_date');
            $table->dropColumn('remind_time');
            $table->dropColumn('reminder_at');
            $table->dropColumn('reminder_duration');
            $table->dropColumn('reminder_activity_id');
            $table->dropColumn('reminder_activity_log_id');
            $table->dropColumn('reminder_note');
        });
    }
};
