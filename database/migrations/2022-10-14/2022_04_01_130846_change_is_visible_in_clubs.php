<?php

use App\Models\Club\Club;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeIsVisibleInClubs extends Migration
{
    public function up()
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->renameColumn('is_visible', 'is_visible_website');
            $table->boolean('is_visible_plan')
                ->default(false)
                ->after('status');
        });

        Club::chunkById(10, function ($clubs) {
            foreach ($clubs as $club) {
                $club->is_visible_plan = $club->is_visible_website;
                $club->save();
            }
        });
    }

    public function down()
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->renameColumn('is_visible_website', 'is_visible');
            $table->dropColumn('is_visible_plan');
        });
    }
}
