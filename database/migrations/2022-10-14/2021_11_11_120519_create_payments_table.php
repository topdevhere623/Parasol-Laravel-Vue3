<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid');
            $table->string('invoice_number', 50);
            $table->string('reference_id', 50)
                ->nullable();

            $table->enum(
                'status',
                [
                    'paid',
                    'failed',
                    'pending',
                    'refund',
                    'unknown',
                ]
            )->default('pending')
                ->index();

            $table->unsignedBigInteger('member_id')
                ->nullable()
                ->index();
            $table->double('total_amount');
            $table->double('subtotal_amount');
            $table->double('discount_amount');
            $table->double('vat_amount');
            $table->double('total_amount_without_vat');
            $table->unsignedBigInteger('payment_method_id')
                ->index();
            $table->unsignedBigInteger('payment_type_id')
                ->index();

            $table->unsignedBigInteger('coupon_id')
                ->index()
                ->nullable();

            $table->string('offer_code', 70)
                ->nullable();

            $table->boolean('is_recurring')
                ->default(0);

            $table->string('attachment', 70);
            $table->date('payment_date')
                ->index()
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('member_id')
                ->references('id')
                ->on('members');

            $table->foreign('payment_method_id')
                ->references('id')
                ->on('payment_methods');

            $table->foreign('payment_type_id')
                ->references('id')
                ->on('payment_types');

            $table->foreign('coupon_id')
                ->references('id')
                ->on('coupons');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
