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
        Schema::create('membership_renewals', function (Blueprint $table) {
            $table->id();

            $table->enum('status', [
                'pending',
                'awaiting_due_date',
                'completed',
            ])
                ->default('pending')
                ->index();
            $table->unsignedBigInteger('booking_id')
                ->nullable()
                ->index();
            $table->unsignedBigInteger('member_id')
                ->index();
            $table->unsignedBigInteger('old_plan_id')
                ->index();
            $table->date('due_date')
                ->nullable()
                ->index();
            $table->boolean('is_30_days_email_sent')
                ->default(false);
            $table->boolean('is_7_days_email_sent')
                ->default(false);
            $table->boolean('is_expired_email_sent')
                ->default(false);
            $table->string('token', 100)
                ->index();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')
                ->references('id')
                ->on('bookings');

            $table->foreign('member_id')
                ->references('id')
                ->on('members');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('membership_renewals');
    }
};
