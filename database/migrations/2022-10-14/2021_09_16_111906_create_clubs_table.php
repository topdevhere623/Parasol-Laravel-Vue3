<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClubsTable extends Migration
{
    public function up()
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('old_id')
                ->index();
            $table->uuid('uuid')
                ->unique();
            $table->string('title');
            $table->string('mc_display_name')
                ->nullable();
            $table->enum('status', [
                'active',
                'inactive',
                'cancelled',
                'paused',
                'in_progress',
            ])->index()
                ->default('inactive');
            $table->boolean('is_visible')
                ->index()
                ->default(false);
            $table->boolean('checkin_availability')
                ->index()
                ->default(false);

            $table->enum('traffic', [
                'green',
                'red',
                'amber',
            ])->default('green');
            $table->boolean('is_always_red')
                ->default(false);
            $table->enum('access_type', [
                'slots',
                'revolving',
            ])
                ->nullable();
            $table->integer('daily_slots')
                ->nullable();
            $table->integer('kids_slots')
                ->nullable();
            $table->boolean('checkin_over_slots')
                ->default(false);
            $table->boolean('display_slots_block')
                ->default(false);
            $table->integer('auto_checkout_after')
                ->nullable();
            $table->string('home_photo')
                ->nullable();
            $table->string('club_photo')
                ->nullable();
            $table->string('checkout_photo')
                ->nullable();
            $table->string('logo')
                ->nullable();
            $table->string('youtube')
                ->nullable();
            $table->text('description')
                ->nullable();
            $table->text('what_members_love')
                ->nullable();
            $table->string('gmap_link')
                ->nullable();
            $table->string('email')
                ->nullable();
            $table->string('phone')
                ->nullable();
            $table->string('address')
                ->nullable();
            $table->unsignedBigInteger('city_id')
                ->nullable()
                ->index();
            $table->string('website')
                ->nullable();
            $table->text('covid_updates')
                ->nullable();
            $table->text('guest_fees')
                ->nullable();
            $table->string('detailed_club_info')
                ->nullable();
            $table->string('contact')
                ->nullable();
            $table->string('opening_hours_notes')
                ->nullable();
            $table->integer('sort')
                ->index()
                ->nullable();
            $table->string('airtable_id')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('city_id')
                ->references('id')
                ->on('cities');
        });
    }

    public function down()
    {
        Schema::dropIfExists('clubs');
    }
}
