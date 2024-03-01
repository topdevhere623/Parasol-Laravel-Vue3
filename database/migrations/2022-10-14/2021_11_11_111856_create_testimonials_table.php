<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestimonialsTable extends Migration
{
    public function up()
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->enum('status', [
                'active',
                'inactive',
            ])->index()
                ->default('inactive');
            $table->string('name');
            $table->string('city');
            $table->text('review');
            $table->string('photo')
                ->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('testimonials');
    }
}
