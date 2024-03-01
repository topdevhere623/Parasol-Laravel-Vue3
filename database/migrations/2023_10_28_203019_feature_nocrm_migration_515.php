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
        Schema::create('crm_pipelines', function (Blueprint $table) {
            $table->id();
            $table->uuid()
                ->index()
                ->unique();
            $table->string('name', 100);
            $table->unsignedInteger('nocrm_id')
                ->nullable()
                ->index();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('crm_steps', function (Blueprint $table) {
            $table->id();
            $table->uuid()
                ->index()
                ->unique();
            $table->string('name', 100);
            $table->unsignedTinyInteger('position')
                ->default(0);
            $table->unsignedInteger('nocrm_id')
                ->nullable()
                ->index();
            $table->foreignId('crm_pipeline_id')
                ->index()
                ->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->text('notes')
                ->after('phone')
                ->nullable();
            $table->foreignId('crm_step_id')
                ->after('step')
                ->index()
                ->nullable()
                ->constrained('crm_steps');

            $table->foreignId('created_by')
                ->nullable()
                ->index()
                ->after('won_at')
                ->constrained('backoffice_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crm_steps');
        Schema::dropIfExists('crm_pipelines');
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['crm_step_id', 'created_by']);
            $table->dropColumn('crm_step_id');
            $table->dropColumn('created_by');
            $table->dropColumn('notes');
        });
    }
};
