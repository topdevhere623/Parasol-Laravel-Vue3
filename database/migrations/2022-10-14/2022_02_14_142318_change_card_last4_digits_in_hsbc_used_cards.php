<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCardLast4DigitsInHsbcUsedCards extends Migration
{
    public function up()
    {
        Schema::table('hsbc_used_cards', function (Blueprint $table) {
            $table->string('card_last4_digits')->change();
        });
    }

    public function down()
    {
        Schema::table('hsbc_used_cards', function (Blueprint $table) {
            $table->integer('card_last4_digits')->change();
        });
    }
}
