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
            model: \App\Models\Reports\ReportLeadTag::class,
            displayName: 'Report Lead Tags',
            routes: ['view', 'export', 'index']
        );
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
};
