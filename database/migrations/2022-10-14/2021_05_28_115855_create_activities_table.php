<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id');
            $table->string('name')
                ->index();
            $table->string('description')
                ->nullable();

            $table->unsignedBigInteger('user_id')
                ->nullable();
            $table->string('user_type');

            $table->unsignedBigInteger('entity_id')
                ->nullable();
            $table->string('entity_type')
                ->nullable();

            $table->json('data')
                ->nullable();

            $table->timestamp('created_at');

            $table->index(['id', 'parent_id']);
            $table->index(['user_id', 'user_type']);
            $table->index(['entity_id', 'entity_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activities');
    }
}
