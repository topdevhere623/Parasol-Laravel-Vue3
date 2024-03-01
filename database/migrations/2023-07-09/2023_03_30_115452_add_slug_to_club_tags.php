<?php

use App\Models\Club\ClubTag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('club_tags', function (Blueprint $table) {
            $table
                ->string('slug')
                ->default('')
                ->after('name');
        });

        foreach (ClubTag::withTrashed()->get() as $clubTag) {
            $clubTag->update([
                'slug' => \Str::slugExtended($clubTag->name),
            ]);
        }
    }

    public function down()
    {
        Schema::table('club_tags', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
