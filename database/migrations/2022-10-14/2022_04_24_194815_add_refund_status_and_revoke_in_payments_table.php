<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRefundStatusAndRevokeInPaymentsTable extends Migration
{
    use \App\Traits\EnumChangeTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->setEnumValues(
            'payments',
            'status',
            [
                'paid',
                'failed',
                'pending',
                'refunded',
                'refund',
                'partial_refunded',
                'other',
                'unknown',
            ],
            false,
            'pending'
        );

        \DB::table('payments')->where('status', 'refund')->update(['status' => 'refunded']);
        \DB::table('payments')->where('payment_type_id', 3)->update(['status' => 'other']);

        $this->setEnumValues(
            'payments',
            'status',
            [
                'paid',
                'failed',
                'pending',
                'refunded',
                'partial_refunded',
                'other',
                'unknown',
            ],
            false,
            'pending'
        );

        Schema::table('payments', function (Blueprint $table) {
            $table->double('refund_amount')
                ->after('total_amount_without_vat');
            $table->dropColumn('attachment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->setEnumValues(
            'payments',
            'status',
            [
                'paid',
                'failed',
                'pending',
                'refunded',
                'refund',
                'unknown',
            ],
            false,
            'pending'
        );

        Schema::table('payments', function (Blueprint $table) {
            $table->date('payment_date')
                ->default(null)
                ->change();
            $table->string('attachment', 70)->after('is_recurring');
        });
    }
}
