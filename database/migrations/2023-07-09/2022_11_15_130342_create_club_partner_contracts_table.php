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
        Schema::create('partner_contracts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('name');
            $table->enum('status', ['active', 'inactive', 'expired'])
                ->default('active')
                ->index();
            $table->enum('type', ['first_year', 'renewal'])
                ->default('first_year')
                ->index();
            $table->foreignId('partner_id')
                ->index()
                ->constrained();
            $table->date('start_date')
                ->index();
            $table->date('expiry_date')
                ->index();

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
        Schema::dropIfExists('partner_contracts');
    }
};
