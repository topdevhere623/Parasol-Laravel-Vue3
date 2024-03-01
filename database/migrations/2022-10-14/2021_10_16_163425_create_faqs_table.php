<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFaqsTable extends Migration
{
    public function up()
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->text('answer');
            $table->unsignedBigInteger('category_id');
            $table->enum('status', [
                'inactive',
                'active',
            ])
                ->default('inactive');

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'status',
                'category_id',
            ]);

            $table->foreign('category_id')
                ->references('id')
                ->on('faq_categories');
        });
    }

    public function down()
    {
        Schema::dropIfExists('faqs');
    }
}
