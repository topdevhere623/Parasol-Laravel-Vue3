<?php

use App\Models\Program;
use App\Models\WebSite\GeneralRule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_rule_program', function (Blueprint $table) {
            $table
                ->foreignId('program_id')
                ->references('id')
                ->on('programs');

            $table->foreignId('general_rule_id')
                ->references('id')
                ->on('general_rules');

            $table->primary(['program_id', 'general_rule_id']);
        });

        $data = [];
        foreach (GeneralRule::pluck('id')->toArray() as $id) {
            $data[] = [
                'program_id' => Program::ADV_PLUS_ID,
                'general_rule_id' => $id,
            ];
            $data[] = [
                'program_id' => Program::ENTERTAINER_SOLEIL_ID,
                'general_rule_id' => $id,
            ];
        }
        DB::table('general_rule_program')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('program_to_general_rule');
    }
};
