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
        Schema::table('member_payment_schedule_payment', function (Blueprint $table) {
            $table->unsignedMediumInteger('payment_month')->index();
        });

        \DB::table('member_payment_schedule_payment')->chunkById(100, function (Illuminate\Support\Collection $items) {
            $items->each(function ($item) {
                $payment = \App\Models\Payments\Payment::withTrashed()->find($item->payment_id, ['payment_date']);
                \DB::table('member_payment_schedule_payment')->where(
                    [
                        'payment_id' => $item->payment_id,
                        'member_payment_schedule_id' => $item->member_payment_schedule_id,
                    ]
                )->update([
                    'payment_month' => $payment->payment_date->format('nY'),
                ]);
            });
        }, 'payment_id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_payment_schedule_payment', function (Blueprint $table) {
            $table->dropColumn('payment_month');
        });
    }
};
