<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPageTitleAndMetaDescriptionToPackages extends Migration
{
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('page_title')
                ->after('show_on_homepage')
                ->nullable();
            $table->text('meta_description')
                ->after('page_title')
                ->nullable();
        });
    }

    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('page_title');
            $table->dropColumn('meta_description');
        });
    }
}
