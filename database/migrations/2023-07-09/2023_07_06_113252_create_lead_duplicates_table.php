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
        Schema::create('lead_duplicates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('duplicate_lead_id');
            $table->enum('status', ['potential_duplicate', 'duplicate', 'not_duplicate'])
                ->default('potential_duplicate');
            $table->text('note')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['lead_id', 'duplicate_lead_id']);
        });

        seed_permissions(\App\Models\Lead\LeadDuplicate::class, 'Lead Duplicates', ['supervisor']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_duplicates');
    }
};
