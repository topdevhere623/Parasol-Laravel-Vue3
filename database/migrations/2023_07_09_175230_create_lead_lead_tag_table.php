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
        Schema::create('lead_lead_tag', function (Blueprint $table) {
            $table->foreignId('lead_id')->constrained();
            $table->foreignId('lead_tag_id')->constrained();
            $table->primary(['lead_id', 'lead_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_lead_tag');
    }
};
