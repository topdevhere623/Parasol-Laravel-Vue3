<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortDisplayOnBookingToMembershipSources extends Migration
{
    public function up()
    {
        Schema::table('membership_sources', function (Blueprint $table) {
            $table->integer('sort')
                ->default(99)
                ->after('title');
            $table->boolean('display_on_booking')
                ->default(false)
                ->index()
                ->after('sort');
        });
    }

    public function down()
    {
        Schema::table('membership_sources', function (Blueprint $table) {
            $table->dropColumn('sort');
            $table->dropColumn('display_on_booking');
        });
    }
}
