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
        Schema::create('partner_tranches', function (Blueprint $table) {
            $table->id();

            $table->enum('status', ['active', 'awaiting_first_visit', 'inactive', 'expired'])
                ->default('active')
                ->index();
            $table->tinyInteger('slots');
            $table->tinyInteger('kids_slots');
            $table->tinyInteger('single_membership_count');
            $table->tinyInteger('family_membership_count');
            $table->foreignId('partner_id')
                ->index()
                ->constrained();
            $table->date('start_date')
                ->nullable();
            $table->date('expiry_date')
                ->nullable();
            $table->foreignId('partner_payment_id')
                ->index()
                ->nullable()
                ->constrained();

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
        Schema::dropIfExists('partner_tranches');
    }
};
