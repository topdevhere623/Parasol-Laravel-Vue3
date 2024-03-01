<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCorporateIdInMemberBillingDetails extends Migration
{
    public function up()
    {
        Schema::table('member_billing_details', function (Blueprint $table) {
            $table->unsignedBigInteger('corporate_id')
                ->nullable()
                ->after('last_name');

            $table->foreign('corporate_id')
                ->references('id')
                ->on('corporates');
        });
    }

    public function down()
    {
        Schema::table('member_billing_details', function (Blueprint $table) {
            $table->dropForeign(['corporate_id']);
            $table->dropColumn(['corporate_id']);
        });
    }
}
