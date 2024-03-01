<?php

use App\Models\GemsApi;
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
        Schema::table('gems_api', function (Blueprint $table) {
            $table->json('request')
                ->nullable()
                ->after('member_id');
        });

        GemsApi::chunkById(100, fn ($items) => $items->each(function (GemsApi $item) {
            $item->request = $item->only('loyal_id', 'aff_id', 'token_id', 'first_name', 'last_name');

            $item->save();
        }));

        Schema::table('gems_api', function (Blueprint $table) {
            $table->dropColumn([
                'aff_id',
                'token_id',
                'first_name',
                'last_name',
                'trn_datetime',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gems_api', function (Blueprint $table) {
            $table->dropColumn('request');
        });
    }
};
