<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('club_id');
            $table->enum('status', [
                'checked_in',
                'checked_out',
                'paid_guest_fee',
                'turned_away',
            ])->default('checked_in');
            $table->tinyInteger('number_of_kids')
                ->unsigned();
            $table->dateTime('checked_in_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('checked_out_at')
                ->nullable()
                ->default(null);
            $table->integer('created_by')
                ->unsigned();
            $table->timestamps();
            $table->softDeletes();

            //            $table->foreign('member_id')
            //                ->references('id')
            //                ->on('members');
            //
            //            $table->foreign('club_id')
            //                ->references('id')
            //                ->on('clubs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checkins');
    }
}
