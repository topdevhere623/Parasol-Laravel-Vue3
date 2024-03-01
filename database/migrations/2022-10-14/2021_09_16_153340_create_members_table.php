<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembersTable extends Migration
{
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('old_id');
            $table->uuid('uuid')
                ->unique();
            $table->unsignedBigInteger('parent_id')
                ->nullable();
            $table->unsignedBigInteger('booking_id')
                ->nullable();
            $table->string('member_id')
                ->nullable();
            $table->string('first_name')
                ->nullable();
            $table->string('last_name')
                ->nullable();
            $table->string('email')
                ->nullable();
            $table->string('business_email')
                ->nullable();
            $table->enum('main_email', [
                'personal_email',
                'business_email',
            ])
                ->nullable()
                ->default('personal_email');
            $table->string('login_email')
                ->nullable();
            $table->date('start_date')
                ->nullable();
            $table->date('end_date')
                ->nullable();
            $table->string('avatar')
                ->nullable();
            $table->date('dob')
                ->nullable();
            $table->enum('membership_status', [
                'active',
                'expired',
                'cancelled',
                'processing',
                'transferred',
                'paused',
                'payment_defaulted_on_hold',
            ])
                ->nullable()
                ->default('processing');
            $table->enum('member_type', [
                'member',
                'partner',
                'junior',
            ]);
            $table->unsignedBigInteger('membership_type_id')
                ->nullable();
            $table->unsignedBigInteger('membership_source_id')
                ->nullable();
            $table->string('corporate_name')
                ->nullable();
            $table->string('club_exec_number')
                ->nullable();
            $table->string('referral_code')
                ->nullable();
            $table->string('offer_code')
                ->nullable();
            $table->string('password')
                ->nullable();
            $table->string('phone')
                ->nullable();
            $table->timestamp('last_seen_at')
                ->nullable();
            $table->string('source')
                ->nullable();
            $table->string('airtable_id')
                ->nullable();
            $table->unsignedBigInteger('program_id')
                ->nullable();
            $table->unsignedBigInteger('package_id')
                ->nullable();
            $table->unsignedBigInteger('plan_id')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['id', 'parent_id']);

            $table->foreign('program_id')
                ->references('id')
                ->on('programs');

            $table->foreign('package_id')
                ->references('id')
                ->on('packages');

            $table->foreign('plan_id')
                ->references('id')
                ->on('plans');

            $table->foreign('membership_type_id')
                ->references('id')
                ->on('membership_types');

            $table->foreign('membership_source_id')
                ->references('id')
                ->on('membership_sources');
        });
    }

    public function down()
    {
        Schema::dropIfExists('members');
    }
}
