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
        $this->setEnumValues(
            'web_form_requests',
            'status',
            [
                'new_request',
                'respond',
                'incoming',
                'assigned',
                'responded',
                'pending',
                'joined',
                'lost',
            ]
        );

        DB::table('web_form_requests')
            ->where('status', 'new_request')
            ->update([
                'status' => 'incoming',
            ]);

        DB::table('web_form_requests')
            ->where('status', 'respond')
            ->update([
                'status' => 'responded',
            ]);

        $this->setEnumValues(
            'web_form_requests',
            'status',
            [
                'incoming',
                'assigned',
                'responded',
                'pending',
                'joined',
                'lost',
            ],
            true,
            'incoming'
        );

        Schema::table('web_form_requests', function (Blueprint $table) {
            $table
                ->foreignId('backoffice_user_id')
                ->nullable()
                ->comment('assigned to sales person')
                ->after('note');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('web_form_requests', function (Blueprint $table) {
            $table->dropColumn('backoffice_user_id');
            $table->dropColumn('member_id');
        });
    }
};
