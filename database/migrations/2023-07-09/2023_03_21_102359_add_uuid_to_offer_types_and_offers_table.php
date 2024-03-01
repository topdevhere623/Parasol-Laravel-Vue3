<?php

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
        Schema::table('offer_types', function (Blueprint $table) {
            $table->uuid()
                ->index()
                ->after('id');
        });

        \App\Models\OfferType::withTrashed()->each(function ($item) {
            $item->uuid = Str::orderedUuid()->toString();
            $item->save();
        });
        Schema::table('offers', function (Blueprint $table) {
            $table->uuid()
                ->index()
                ->after('id');
        });

        \App\Models\Offer::withTrashed()->each(function ($item) {
            $item->uuid = Str::orderedUuid()->toString();
            $item->save();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offer_types', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
