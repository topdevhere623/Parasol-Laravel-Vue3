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
        Schema::create('crm_emails', function (Blueprint $table) {
            $table->id();
            $table->uuid()
                ->index();

            $table->string('to')
                ->nullable();
            $table->string('from')
                ->nullable();
            $table->string('from_name')
                ->nullable();
            $table->string('cc')
                ->nullable();
            $table->string('bcc')
                ->nullable();
            $table->string('subject')
                ->nullable();
            $table->text('content')
                ->nullable();
            $table->json('threaded_content')
                ->nullable();
            $table->boolean('has_more_content')
                ->default(false);
            $table->boolean('is_read')
                ->default(false);
            $table->unsignedInteger('status')
                ->nullable();

            $table->foreignId('lead_id')
                ->nullable()
                ->index();
            $table->foreignId('crm_comment_id')
                ->nullable()
                ->index();
            $table->foreignId('backoffice_user_id')
                ->nullable()
                ->index();
            $table->unsignedBigInteger('nocrm_id')
                ->nullable()
                ->index();
            $table->unsignedInteger('nocrm_lead_id')
                ->nullable()
                ->index();
            $table->unsignedBigInteger('nocrm_owner_id')
                ->nullable()
                ->index();

            $table->timestamp('scheduled_at')
                ->nullable();
            $table->timestamp('sent_at')
                ->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crm_emails');
    }
};
