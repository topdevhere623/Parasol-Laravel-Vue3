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
        Schema::create('crm_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid()
                ->index()
                ->unique();

            $table->unsignedInteger('nocrm_id')
                ->nullable();
            $table->unsignedInteger('nocrm_lead_id')
                ->nullable()
                ->index();
            $table->unsignedInteger('nocrm_user_id')
                ->nullable();

            $table->unsignedInteger('lead_id')
                ->index();

            $table->unsignedBigInteger('historyable_id')
                ->nullable()
                ->index();
            $table->string('historyable_type')
                ->nullable()
                ->index();

            $table->unsignedInteger('user_id')
                ->comment('User who made the action')
                ->nullable();

            $table->enum('action_type', [
                'client_assigned',
                'step_changed',
                'status_changed',
                'amount_set',
                'user_assigned',
                'lead_created',
                'lead_edited',
                'comment_added',
                'email_sent',
            ])
                ->nullable();

            $table->json('action_item')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crm_histories');
    }
};
