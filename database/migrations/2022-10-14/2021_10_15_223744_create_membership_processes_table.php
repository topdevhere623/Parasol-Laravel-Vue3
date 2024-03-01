<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembershipProcessesTable extends Migration
{
    public function up()
    {
        Schema::create('membership_processes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('used_member_id')
                ->nullable();
            $table->unsignedBigInteger('member_id');
            $table->string('title');
            $table->text('note')
                ->nullable();
            $table->enum('status', [
                'pending',
                'complete',
                'cancelled',
            ])
                ->default('pending');
            $table->date('action_due_date')
                ->nullable();
            $table->string('members')
                ->nullable();
            $table->string('file')
                ->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'member_id',
                'status',
            ]);

            $table->foreign('member_id')
                ->references('id')
                ->on('members');

            $table->foreign('used_member_id')
                ->references('id')
                ->on('members');
        });
    }

    public function down()
    {
        Schema::dropIfExists('membership_processes');
    }
}
