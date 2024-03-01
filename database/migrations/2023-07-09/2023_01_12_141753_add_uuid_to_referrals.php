<?php

use App\Models\Referral;
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
        Schema::table('referrals', function (Blueprint $table) {
            $table->uuid('uuid')
                ->after('id')
                ->index();
        });
        Referral::select('id')->chunkById(1000, function ($referrals) {
            $referrals->each(function ($referral) {
                $referral->uuid = \Str::orderedUuid()->toString();
                $referral->save();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
