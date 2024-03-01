<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('old_id')
                ->index()
                ->nullable();
            $table->string('reference_id', 50)
                ->nullable()
                ->index();
            $table->uuid('uuid')
                ->index();
            $table->unsignedBigInteger('member_id')
                ->index()
                ->nullable();

            $table->string('first_name', 100);
            $table->string('email', 100);
            $table->string('phone', 30)
                ->nullable();

            $table->unsignedBigInteger('plan_id')
                ->nullable()
                ->index();

            $table->unsignedBigInteger('payment_method_id')
                ->index()
                ->nullable();

            $table->integer('number_of_children');
            $table->integer('number_of_juniors');

            $table->double('total_price');

            $table->double('plan_amount');
            $table->double('extra_child_amount');
            $table->double('extra_junior_amount');
            $table->double('subtotal_amount');
            $table->double('coupon_amount');
            $table->double('gift_card_amount');
            $table->double('vat_amount');

            $table->unsignedBigInteger('coupon_id')->nullable()->index();
            $table->string('gift_card_number', 100)->nullable();

            $table->string('membership_source', 100)->nullable();
            $table->string('membership_source_other', 100)->nullable();

            $table->tinyInteger('step')
                ->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('member_id')
                ->references('id')
                ->on('members');

            $table->foreign('plan_id')
                ->references('id')
                ->on('plans');

            $table->foreign('payment_method_id')
                ->references('id')
                ->on('payment_methods');

            $table->foreign('coupon_id')
                ->references('id')
                ->on('coupons');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}
