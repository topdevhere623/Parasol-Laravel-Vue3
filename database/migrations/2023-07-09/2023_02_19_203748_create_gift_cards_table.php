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
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->uuid()
                ->index();
            $table->enum('status', ['active', 'inactive'])
                ->default('active')
                ->index();
            $table->string('title', 70);
            $table->string('invoice_title', 70);
            $table->string('code', 70);
            $table->timestamps();
            $table->softDeletes();
        });

        \App\Models\GiftCard::create([
            'title' => 'GEMS Points',
            'invoice_title' => 'Rewards points',
            'code' => 'gems_points',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gift_cards');
    }
};
