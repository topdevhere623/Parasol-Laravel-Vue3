<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFirstNameInBookings extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->renameColumn('first_name', 'name');
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->renameColumn('name', 'first_name');
        });
    }
}
