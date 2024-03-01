<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageInfosTable extends Migration
{
    public function up()
    {
        Schema::create('package_infos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle');
            $table->string('image');
            $table->text('description');
            $table->integer('sort')
                ->default(1);
            $table->string('url');
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
        Schema::dropIfExists('package_infos');
    }
}
