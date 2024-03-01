<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')
                ->unique();
            $table->string('name');
            $table->string('passkit_id')
                ->nullable();
            $table->string('prefix');
            $table->boolean('generate_passes')
                ->default(false);
            $table->string('email', 40)
                ->nullable();
            $table->string('member_portal_logo', 50)
                ->nullable();
            $table->string('member_portal_header_color', 30)
                ->nullable();
            $table->string('color', 30)
                ->nullable();
            $table->string('source')
                ->nullable();
            $table->string('password')
                ->nullable();
            $table->enum('status', [
                'inactive',
                'active',
            ])
                ->default('active');
            $table->timestamp('last_seen')
                ->nullable();
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
        Schema::dropIfExists('programs');
    }
}
