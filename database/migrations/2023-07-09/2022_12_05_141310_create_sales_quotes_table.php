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
        Schema::create('sales_quotes', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('corporate_client');
            $table->integer('clubs_count')->default(0);
            $table->integer('singles_count')->default(0);
            $table->integer('families_count')->default(0);
            $table->integer('duration')->default(0);

            $table->text('corporate_contact_name');
            $table->text('corporate_contact_number');
            $table->text('corporate_contact_email');
            $table->boolean('display_monthly_value');
            $table->boolean('display_daily_per_club');

            $table->foreignId('sales_person_id')
                ->constrained('backoffice_users');
            $table->json('json_data')->nullable();
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
        Schema::dropIfExists('sales_quotes');
    }
};
