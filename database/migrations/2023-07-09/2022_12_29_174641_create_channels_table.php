<?php

use App\Models\Channel;
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
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->char('title', 32);
            $table->enum('status', [
                'active',
                'inactive',
                'expired',
            ])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
        // заполняем
        DB::insert(
            '
            INSERT INTO channels (id, title)
            VALUES ('.Channel::MEMBER_REFERRAL_ID.", '".Channel::MEMBER_REFERRAL_NAME."'), (2, 'Gems HR')
        "
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('channels');
    }
};
