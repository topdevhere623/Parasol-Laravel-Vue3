<?php

use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        seed_permissions(
            \App\Models\Reports\Lead\TeamPerformanceLeadReport::class,
            'Team Performance Report',
            ['supervisor']
        );
        seed_permissions(
            \App\Models\Reports\Lead\CompanyPerformanceLeadReport::class,
            'Company Performance Report',
            ['supervisor']
        );
        seed_permissions(
            \App\Models\Reports\Lead\TeamActivityLeadReport::class,
            'Team Activity Report',
            ['supervisor']
        );

        Schema::table('leads', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->renameColumn('won_at', 'closed_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->renameColumn('closed_at', 'won_at');
        });
    }
};
