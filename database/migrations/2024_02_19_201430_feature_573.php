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
        Schema::table('payment_mamo_links', function (Blueprint $table) {
            $table->string('payable_type')
                ->after('link')
                ->nullable();
            $table->renameColumn('payment_id', 'payable_id');
        });

        \DB::table('payment_mamo_links')
            ->update([
                'payable_type' => \App\Models\Payments\Payment::class,
            ]);

        \DB::table('member_payment_schedules')
            ->update([
                'payment_method_id' => 2,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_mamo_links', function (Blueprint $table) {
            $table->dropColumn('payable_type');
            $table->renameColumn('payable_id', 'payment_id');
        });
    }
};
