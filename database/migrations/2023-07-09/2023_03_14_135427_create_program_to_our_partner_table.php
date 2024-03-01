<?php

use App\Models\OurPartner;
use App\Models\Program;
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
        Schema::create('our_partner_program', function (Blueprint $table) {
            $table
                ->foreignId('program_id')
                ->references('id')
                ->on('programs');

            $table->foreignId('our_partner_id')
                ->references('id')
                ->on('our_partners');

            $table->primary(['program_id', 'our_partner_id']);
        });

        $data = [];
        foreach (OurPartner::pluck('id')->toArray() as $id) {
            $data[] = [
                'program_id' => Program::ADV_PLUS_ID,
                'our_partner_id' => $id,
            ];
            $data[] = [
                'program_id' => Program::ENTERTAINER_SOLEIL_ID,
                'our_partner_id' => $id,
            ];
        }
        DB::table('our_partner_program')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('our_partner_program');
    }
};
