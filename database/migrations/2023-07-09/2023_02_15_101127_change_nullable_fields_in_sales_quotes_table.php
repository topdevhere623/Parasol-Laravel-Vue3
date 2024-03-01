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
        Schema::table('sales_quotes', function (Blueprint $table) {
            $table->text('corporate_contact_name')
                ->nullable()
                ->change();
            $table->text('corporate_contact_number')
                ->nullable()
                ->change();
            $table->text('corporate_contact_email')
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_quotes', function (Blueprint $table) {
            //
        });
    }
};
