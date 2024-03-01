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
        Schema::create('partner_payments', function (Blueprint $table) {
            $table->id();
            $table->string('airtable_id')->nullable();
            $table->string('cheque_number')->nullable();
            $table->foreignId('partner_id')
                ->index()
                ->nullable()
                ->constrained();
            $table->enum('status', [
                'cashed',
                'overdue',
                'outstanding',
                'cancelled',
                'postponed',
                'future payment',
                'forecasted',
            ])->default('outstanding');
            $table->enum('type', ['cheque', 'bank_transfer', 'credit_card'])
                ->default('cheque')
                ->index();
            $table->double('amount');
            $table->date('date');
            $table->enum('issued_by', [
                'software',
                'loyalty',
                'mashreq',
            ])->nullable();
            $table->enum('bank', [
                'rakbank',
                'mashreq',
            ])->nullable();
            $table->string('tax_invoice', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_payments');
    }
};
