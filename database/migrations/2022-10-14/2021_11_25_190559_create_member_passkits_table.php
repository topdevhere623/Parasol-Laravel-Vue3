<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberPasskitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_passkits', function (Blueprint $table) {
            $table->id();
            $table->string('passkit_id', 70)
                ->index();
            $table->enum('status', ['PASS_ISSUED', 'PASS_INSTALLED', 'PASS_UNINSTALLED', 'PASS_INVALIDATED'])->default(
                'PASS_ISSUED'
            )->index();
            $table->boolean('has_apple_installed')->default(0);
            $table->boolean('has_google_installed')->default(0);
            $table->boolean('has_apple_uninstalled')->default(0);
            $table->boolean('has_google_uninstalled')->default(0);
            $table->bigInteger('member_id')
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
        Schema::dropIfExists('member_passkits');
    }
}
