<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    private $table = 'member_club_favorite';

    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->unsignedBigInteger('club_id')
                ->index();
            $table->unsignedBigInteger('member_id')
                ->index();
            $table->primary(['member_id', 'club_id']);
            $table->timestamp('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists($this->table);
    }
};
