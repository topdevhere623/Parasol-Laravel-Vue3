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
            \App\Models\Reports\ReportMonthlySale::class,
            'Monthly Sale Report',
            ['supervisor'],
            ['index', 'export']
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
