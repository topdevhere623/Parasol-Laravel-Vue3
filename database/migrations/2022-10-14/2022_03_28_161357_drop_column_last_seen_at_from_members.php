<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnLastSeenAtFromMembers extends Migration
{
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('last_seen_at');
        });
    }

    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->timestamp('last_seen_at')
                ->nullable();
        });
    }
}
