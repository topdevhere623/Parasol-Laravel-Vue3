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
        Schema::table('leads', function (Blueprint $table) {
            $table->enum('status', ['won', 'todo', 'standby', 'cancelled', 'lost'])
                ->default('todo')
                ->after('uuid')
                ->index();
            $table->enum(
                'step',
                ['incoming', 'unanswered', 'answered', 'appointment', 'quotation_sent', 'closing', 'lost']
            )
                ->default('incoming')
                ->after('status')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('status', 'step');
        });
    }
};
