<?php

use App\Models\Partner\Partner;
use App\Models\Partner\PartnerContract;
use App\Models\Partner\PartnerTranche;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        seed_permissions(Partner::class, 'Partners');
        seed_permissions(PartnerTranche::class, 'Partner Tranches');
        seed_permissions(PartnerContract::class, 'Partner Contracts');

        \DB::table('permissions')->whereRaw('name LIKE \'%Club\'')
            ->update(['name' => \DB::raw("REPLACE(name, 'App\\\\Models\\\\Club', 'App\\\\Models\\\\Club\\\\Club')")]);

        \DB::table('permissions')->whereRaw('name LIKE \'%ClubTag\'')
            ->update(
                ['name' => \DB::raw("REPLACE(name, 'App\\\\Models\\\\ClubTag', 'App\\\\Models\\\\Club\\\\ClubTag')")]
            );

        \DB::table('permissions')->whereRaw('name LIKE \'%Checkin\'')
            ->update(
                [
                    'name' => \DB::raw(
                        "REPLACE(name, 'App\\\\Models\\\\Checkin', 'App\\\\Models\\\\Club\\\\Checkin')"
                    ),
                ]
            );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
