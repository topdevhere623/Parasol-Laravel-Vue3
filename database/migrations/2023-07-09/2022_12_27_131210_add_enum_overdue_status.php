<?php

use App\Traits\EnumChangeTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use EnumChangeTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('membership_processes', function (Blueprint $table) {
            $this->setEnumValues(
                'membership_processes',
                'status',
                [
                    'pending',
                    'complete',
                    'cancelled',
                    'overdue',
                ],
                false,
                'pending'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('membership_processes', function (Blueprint $table) {
            $this->setEnumValues(
                'membership_processes',
                'status',
                [
                    'pending',
                    'complete',
                    'cancelled',
                ],
                false,
                'pending'
            );
        });
    }
};
