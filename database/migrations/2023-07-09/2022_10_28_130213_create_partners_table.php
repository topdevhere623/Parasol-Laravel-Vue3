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
        Schema::create('partners', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('airtable_id');

            $table->enum('status', [
                [
                    'active',
                    'in_progress',
                    'paused',
                    'inactive',
                    'cancelled',
                    'discontinued',
                    'negotiation',
                    'declined',
                    'incoming',
                    'pipeline',
                    'pay_as_you_go',
                    'sales_partner',
                ],
            ])
                ->default('active')
                ->index();
            $table->boolean('is_pooled_access');
            $table->tinyInteger('single_membership_count');
            $table->double('single_membership_price');
            $table->tinyInteger('family_membership_count');
            $table->double('family_membership_price');
            $table->date('contract_expiry')
                ->nullable();
            $table->text('notes')
                ->nullable();
            $table->string('website', 100)
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
        Schema::dropIfExists('partners');
    }
};
