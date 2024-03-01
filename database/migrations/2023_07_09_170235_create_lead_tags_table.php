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
        Schema::create('lead_tags', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('name', 70);
            $table->foreignId('lead_category_id')
                ->nullable()
                ->index()
                ->constrained();
            $table->unsignedBigInteger('nocrm_id')
                ->nullable()
                ->index();
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
        Schema::dropIfExists('lead_tags');
    }
};
