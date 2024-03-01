<?php

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
        Schema::table('testimonials', function (Blueprint $table) {
            $table
                ->unsignedBigInteger('program_id')
                ->nullable()
                ->comment('Program in which testimonial is shown')
                ->after('status');
        });

        DB::table('testimonials')->update([
            'program_id' => Program::ADV_PLUS_ID,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn('program_id');
        });
    }
};
