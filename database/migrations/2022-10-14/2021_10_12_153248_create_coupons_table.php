<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->enum('status', [
                'inactive',
                'active',
                'expired',
            ])
                ->default('inactive');
            $table->string('code')
                ->unique();
            $table->enum('type', [
                'percentage',
                'fixed',
            ])
                ->default('percentage');
            $table->double('amount');
            $table->string('member_intro')
                ->nullable();
            $table->unsignedBigInteger('member_id')
                ->nullable();
            $table->string('owner');
            $table->string('corporate_name')
                ->nullable();
            $table->string('note')
                ->nullable();
            $table->integer('usage_limit');
            $table->integer('number_of_used')
                ->default(0);
            $table->string('email_domain')
                ->nullable();
            $table->date('expiry_date')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('member_id')
                ->references('id')
                ->on('members');
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}
