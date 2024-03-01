<?php

use App\Models\Program;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    private $table = 'programs_to_clubs_sort';

    public function up()
    {
        // create the table
        Schema::create($this->table, function (Blueprint $table) {
            $table
                ->foreignId('program_id')
                ->index();
            $table
                ->foreignId('club_id')
                ->index();
            $table->integer('sort')
                ->index()
                ->default(1);
        });
        DB::unprepared("ALTER TABLE {$this->table} ADD PRIMARY KEY (`program_id`,  `club_id`)");

        // fill the table
        foreach (
            [
                Program::ENTERTAINER_SOLEIL_ID,
                Program::ADV_PLUS_ID,
                Program::ENTERTAINER_HSBC,
            ] as $programId
        ) {
            $data = [];
            foreach (
                DB::table('clubs')
                    ->select(['id', 'sort'])
                    ->whereNull('deleted_at')
                    ->get() as $club
            ) {
                $sort = $club->sort ?: 100000;
                $data[] = "({$programId}, {$club->id}, {$sort})";
            }
            DB::insert("INSERT INTO {$this->table} (program_id, club_id, sort) VALUES ".implode(',', $data));
        }

        // delete clubs.sort column
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn('sort');
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->table);
    }
};
