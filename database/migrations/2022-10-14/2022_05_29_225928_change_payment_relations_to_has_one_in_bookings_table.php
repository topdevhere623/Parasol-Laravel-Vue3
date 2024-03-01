<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePaymentRelationsToHasOneInBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_id')
                ->nullable()
                ->after('plan_id')
                ->index();

            $table->foreign('payment_id')
                ->references('id')
                ->on('payments');
        });

        \App\Models\Booking::select('id')->chunkById(100, function ($items) {
            $items->each(function ($item) {
                $rel = \DB::table('booking_payment')
                    ->where('booking_id', $item->id)
                    ->latest('payment_id')
                    ->first();
                if ($rel) {
                    $item->payment_id = $rel->payment_id;
                    $item->save();
                }
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
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('payment_id');
        });
    }
}
