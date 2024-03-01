<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeStatusAndRemoveJsonResponseFromPaymentTransactionsTable extends Migration
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
            'payment_transactions',
            'status',
            [
                'failed',
                'paid',
                'authorized',
                'refunded',

                'pending',
                'success',
                'fail',
            ],
            false,
            'pending'
        );

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->uuid('uuid')
                ->after('id')
                ->index();
            $table->dropColumn('response_json');
            $table->string('description')
                ->after('amount')
                ->nullable();
            $table->enum('type', ['capture', 'authorize', 'refund', 'void'])
                ->after('status')
                ->default('capture');
        });

        \App\Models\Payments\PaymentTransaction::select('id')->chunkById(100, function ($items) {
            $items->each(function ($item) {
                $item->uuid = \Str::orderedUuid();
                $item->save();
            });
        });

        \DB::table('payment_transactions')
            ->where('status', 'paid')
            ->update([
                'type' => 'capture',
                'status' => 'success',
            ]);

        \DB::table('payment_transactions')
            ->where('status', 'authorized')
            ->update([
                'type' => 'authorize',
                'status' => 'success',
            ]);

        \DB::table('payment_transactions')
            ->where('status', 'pending')
            ->update([
                'type' => 'capture',
            ]);

        \DB::table('payment_transactions')
            ->where('status', 'failed')
            ->update([
                'type' => 'capture',
                'status' => 'fail',
            ]);

        \DB::update(
            'UPDATE payment_transactions SET deleted_at = (SELECT deleted_at FROM payments WHERE payments.id = payment_transactions.payment_id)'
        );

        $this->setEnumValues(
            'payment_transactions',
            'status',
            [
                'pending',
                'success',
                'fail',
            ],
            false,
            'pending'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->setEnumValues(
            'payment_transactions',
            'status',
            [
                'pending',
                'failed',
                'paid',
                'authorized',
            ],
            false,
            'pending'
        );

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('uuid');
            $table->json('response_json')
                ->after('amount')
                ->nullable();
        });
    }
}
