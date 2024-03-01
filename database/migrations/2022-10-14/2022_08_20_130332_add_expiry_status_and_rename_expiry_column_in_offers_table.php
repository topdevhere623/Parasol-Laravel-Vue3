<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use \App\Traits\EnumChangeTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->setEnumValues(
            'offers',
            'status',
            [
                'active',
                'inactive',
                'expired',
            ],
            false,
            'active'
        );

        Schema::table('offers', function (Blueprint $table) {
            $table->renameColumn('offer_expiry', 'expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->setEnumValues(
            'offers',
            'status',
            [
                'active',
                'inactive',
            ],
            false,
            'active'
        );

        Schema::table('offers', function (Blueprint $table) {
            $table->renameColumn('expiry_date', 'offer_expiry');
        });
    }
};
