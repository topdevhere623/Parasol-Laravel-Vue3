<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateNullableColumnsInPrograms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('passkit_faq_url_ios')->change()->nullable();
            $table->string('passkit_faq_url_android')->change()->nullable();
            $table->string('terms_and_conditions_url')->change()->nullable();
            $table->string('faq_page_url')->change()->nullable();
            $table->string('club_guide_url')->change()->nullable();
            $table->string('whatsapp_url')->change()->nullable();

            $table->text('contact_us_page')
                ->change()
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('passkit_faq_url_ios')->change();
            $table->string('passkit_faq_url_android')->change();
            $table->string('terms_and_conditions_url')->change();
            $table->string('faq_page_url')->change();
            $table->string('club_guide_url')->change();
            $table->string('whatsapp_url')->change();

            $table->text('contact_us_page')->change();
        });
    }
}
