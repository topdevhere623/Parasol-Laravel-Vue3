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
            $table->renameColumn('vat', 'vat_type');

            $table->unsignedBigInteger('renewal_package_id')
                ->nullable()
                ->index()
                ->after('package_id');
            $table->boolean('show_start_date_on_booking')
                ->after('is_coupon_conditional_purchase')
                ->default(true);
            $table->enum('renewal_email_type', [
                'default',
                'corporate',
                'special_offer',
            ])
                ->after('status')
                ->default('default');

            $table->foreign('renewal_package_id')
                ->references('id')
                ->on('packages');
        });

        Activity::disable();

        \App\Models\Plan::with('program')->each(function (App\Models\Plan $item) {
            if ($item->program->id != 1) {
                $item->renewal_package_id = $item->package_id;
            }
            $item->save();
        });

        \DB::table('plans')->update(['renewal_email_type' => 'corporate']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->renameColumn('vat_type', 'vat');
            $table->dropColumn('renewal_package_id');
            $table->dropColumn('show_start_date_on_booking');
            $table->dropColumn('renewal_package_id');
            $table->dropColumn('renewal_email_type');
        });
    }
};
