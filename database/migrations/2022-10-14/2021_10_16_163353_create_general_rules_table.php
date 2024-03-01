<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralRulesTable extends Migration
{
    public function up()
    {
        Schema::create('general_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')
                ->nullable();
            $table->enum('status', [
                'inactive',
                'active',
            ])
                ->default('inactive');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('general_rules');
    }
}
