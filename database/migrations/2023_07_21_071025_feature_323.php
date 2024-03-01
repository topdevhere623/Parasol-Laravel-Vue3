<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    private string $table = 'bookings';
    private string $table1 = 'plans';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        Schema::table($this->table, function (Blueprint $table) {
            $table
                ->enum('step_tmp', ['payment', 'billing_details', 'membership_details', 'completed', 'default'])
                ->nullable()
                ->default('default')
                ->after('step');
        });

        \Illuminate\Support\Facades\DB::statement("
        update bookings
        set step_tmp = CASE
            WHEN step = 1 THEN 'default'
            WHEN step = 2 THEN 'payment'
            WHEN step = 3 THEN 'billing_details'
            WHEN step = 4 THEN 'membership_details'
            WHEN step = 5 THEN 'completed'
            END
        ;
        ");

        Schema::table($this->table, function (Blueprint $table) {
            $table->dropColumn('step');
        });

        \Illuminate\Support\Facades\DB::statement('
        alter table bookings rename column step_tmp to step;
        ');

        Schema::table($this->table1, function (Blueprint $table) {
            $table->boolean('is_giftable')->default(true)->after('sort');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        Schema::table($this->table, function (Blueprint $table) {
            $table->tinyInteger('step_tmp')->default(1)->after('step');
        });

        \Illuminate\Support\Facades\DB::statement("
        update bookings
        set step_tmp = CASE
            WHEN step = 'default' THEN 1
            WHEN step = 'payment' THEN 2
            WHEN step = 'billing_details' THEN 3
            WHEN step = 'membership_details' THEN 4
            WHEN step = 'completed' THEN 5
            END
        ;
        ");

        Schema::table($this->table, function (Blueprint $table) {
            $table->dropColumn('step');
        });

        \Illuminate\Support\Facades\DB::statement('
        alter table bookings rename column step_tmp to step;
        ');

        Schema::table($this->table1, function (Blueprint $table) {
            $table->dropColumn('is_giftable');
        });
    }
};
