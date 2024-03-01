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
        Schema::table('package_infos', function (Blueprint $table) {
            $table
                ->boolean('is_popular')
                ->nullable()
                ->after('status');
            $table
                ->enum('type', ['link', 'package', 'corporate_offer'])
                ->default('link')
                ->after('is_popular');
            $table
                ->foreignId('program_id')
                ->index()
                ->nullable()
                ->after('id')
                ->constrained();
            $table
                ->foreignId('package_id')
                ->comment(
                    'If type is package, then there should be a link to the booking of this package'
                )
                ->index()
                ->nullable()
                ->after('program_id')
                ->constrained();
        });
        Schema::table('package_infos', function (Blueprint $table) {
            $table->string('url')
                ->nullable()
                ->change();
        });

        DB::table('package_infos')->update(['program_id' => Program::ADV_PLUS_ID]);
        DB::table('package_infos')->insert([
            [
                'title' => 'Corporate Membership',
                'subtitle' => 'corporate and volume packages',
                'image' => '',
                'description' => '<h4 class="fs-4 my-4">INDIVIDUAL</h4><p>by request</p>',
                'sort' => 3,
                'status' => 'active',
                'is_popular' => false,
                'type' => 'corporate_offer',
                'program_id' => Program::ADV_PLUS_ID,
                'package_id' => null,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_infos', function (Blueprint $table) {
            $table->dropColumn('is_popular');
            $table->dropColumn('type');
            $table->dropColumn('program_id');
        });
    }
};
