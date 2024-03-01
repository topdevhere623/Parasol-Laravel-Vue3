<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusCanceledAtToHsbcUsedCards extends Migration
{
    public function up()
    {
        Schema::table('hsbc_used_cards', function (Blueprint $table) {
            $table->enum('status', [
                'completed',
                'cancelled',
                'refunded',
            ])
                ->default('completed')
                ->after('id');
            $table->timestamp('canceled_at')
                ->nullable()
                ->after('card_expiry_date');
            $table->unsignedDouble('refund_amount')
                ->nullable()
                ->after('canceled_at');
            $table->timestamp('refunded_at')
                ->nullable()
                ->after('refund_amount');
        });
    }

    public function down()
    {
        Schema::table('hsbc_used_cards', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('canceled_at');
            $table->dropColumn('refunded_at');
        });
    }
}
