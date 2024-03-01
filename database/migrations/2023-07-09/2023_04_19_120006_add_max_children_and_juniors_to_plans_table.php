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
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedSmallInteger('number_of_free_children')
                ->after('show_children_block');
            $table->unsignedSmallInteger('number_of_free_juniors')
                ->after('extra_child_price');
        });

        DB::table('plans')->update([
            'number_of_free_children' => DB::raw('number_of_children'),
            'number_of_free_juniors' => DB::raw('number_of_juniors'),
        ]);

        Schema::table('plans', function (Blueprint $table) {
            $table->renameColumn('number_of_children', 'number_of_allowed_children');
            $table->renameColumn('number_of_juniors', 'number_of_allowed_juniors');
        });

        DB::table('plans')->update([
            'number_of_allowed_children' => 4,
            'number_of_allowed_juniors' => 4,
        ]);

        DB::table('plans')->update([
            'number_of_allowed_children' => 4,
            'number_of_allowed_juniors' => 4,
        ]);

        Schema::table('plans', function (Blueprint $table) {
            $table->renameColumn('number_of_adults', 'is_partner_available');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('is_partner_available')
                ->change();
        });

        DB::table('plans')->update([
            'is_partner_available' => DB::raw('is_partner_available - 1'),
        ]);

        Schema::table('plans', function (Blueprint $table) {
            $table->double('extra_child_price')->default(0)->nullable(false)->change();
            $table->double('extra_junior_price')->default(0)->nullable(false)->change();
            $table->unsignedSmallInteger('number_of_allowed_children')->default(0)->nullable(false)->change();
            $table->unsignedSmallInteger('number_of_allowed_juniors')->default(0)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            //
        });
    }
};
