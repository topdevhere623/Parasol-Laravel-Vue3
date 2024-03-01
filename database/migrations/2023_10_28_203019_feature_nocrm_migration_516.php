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
        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->uuid()
                ->index()
                ->unique();
            $table->unsignedBigInteger('parent_id')
                ->nullable()
                ->index();
            $table->string('name', 100);
            $table->string('icon', 100)
                ->nullable();
            $table->string('color', 100)
                ->nullable();
            $table->string('type', 100)
                ->nullable();
            $table->boolean('is_disabled')
                ->default(false);
            $table->unsignedTinyInteger('position')
                ->nullable()
                ->default(0);
            $table->unsignedInteger('nocrm_id')
                ->nullable()
                ->index();
            $table->unsignedInteger('nocrm_parent_id')
                ->nullable()
                ->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('crm_comments', function (Blueprint $table) {
            $table->id();
            $table->uuid()
                ->index()
                ->unique();
            $table->unsignedBigInteger('commentable_id')
                ->index();
            $table->string('commentable_type')
                ->index();
            $table->foreignId('backoffice_user_id')
                ->nullable()
                ->index();
            $table->foreignId('crm_activity_id')
                ->nullable()
                ->index();
            $table->boolean('is_pinned')
                ->default(false);
            $table->text('content')
                ->nullable();
            $table->json('extended_info')
                ->nullable();

            $table->unsignedInteger('nocrm_id')
                ->nullable()
                ->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index('created_at');
        });

        Schema::create('crm_attachments', function (Blueprint $table) {
            $table->id();
            $table->uuid()
                ->index()
                ->unique();
            $table->unsignedBigInteger('attachable_id')
                ->index();
            $table->string('attachable_type')
                ->index();
            $table->string('name');
            $table->text('url')
                ->nullable();
            $table->text('permalink')
                ->nullable();
            $table->string('content_type')
                ->nullable();
            $table->string('kind')
                ->nullable(); // dropbox

            $table->unsignedInteger('nocrm_id')
                ->nullable()
                ->index();
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
        Schema::dropIfExists('crm_activities');
        Schema::dropIfExists('crm_comments');
        Schema::dropIfExists('crm_attachments');
    }
};
