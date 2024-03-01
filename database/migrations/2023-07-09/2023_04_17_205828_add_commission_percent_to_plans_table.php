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
            $table->unsignedDouble('price_third_party_commission_percent')
                ->after('price');
            $table->unsignedDouble('extra_child_third_party_commission_percent')
                ->after('extra_child_price');
            $table->unsignedDouble('extra_junior_third_party_commission_percent')
                ->after('extra_junior_price');
        });

        $plans = [
            'EXCLUSIVE GEMS REWARDS PLUS | Single | 3 clubs membership AED 2,220'
            => [
                'price_third_party_commission_percent' => 9.234234234,
                'extra_child_third_party_commission_percent' => 9.234234234,
                'extra_junior_third_party_commission_percent' => 9.234234234,
            ],
            'EXCLUSIVE GEMS REWARDS PLUS | Family | 3 clubs membership AED 4,380'
            => [
                'price_third_party_commission_percent' => 8.675799087,
                'extra_child_third_party_commission_percent' => 9.234234234,
                'extra_junior_third_party_commission_percent' => 9.234234234,
            ],
            'EXCLUSIVE GEMS REWARDS PLUS | Single | 5 clubs membership AED 2,580'
            => [
                'price_third_party_commission_percent' => 8.914728682,
                'extra_child_third_party_commission_percent' => 8.914728682,
                'extra_junior_third_party_commission_percent' => 8.914728682,
            ],
            'EXCLUSIVE GEMS REWARDS PLUS | Family | 5 clubs membership AED 5,100'
            => [
                'price_third_party_commission_percent' => 8.823529412,
                'extra_child_third_party_commission_percent' => 8.914728682,
                'extra_junior_third_party_commission_percent' => 8.914728682,
            ],
            'EXCLUSIVE GEMS REWARDS PLUS | Single | 10 clubs membership AED 2,988'
            => [
                'price_third_party_commission_percent' => 9.136546185,
                'extra_child_third_party_commission_percent' => 8.53125,
                'extra_junior_third_party_commission_percent' => 9.136546185,
            ],
            'EXCLUSIVE GEMS REWARDS PLUS | Family | 10 clubs membership AED 5,940'
            => [
                'price_third_party_commission_percent' => 9.090909091,
                'extra_child_third_party_commission_percent' => 8.53125,
                'extra_junior_third_party_commission_percent' => 9.136546185,
            ],
            'Single HSBC ENTERTAINER soleil | complimentary + Family upgrade'
            => [
                'price_third_party_commission_percent' => 20,
                'extra_child_third_party_commission_percent' => 20,
                'extra_junior_third_party_commission_percent' => 20,
            ],
            'Single HSBC ENTERTAINER soleil | complimentary'
            => [
                'price_third_party_commission_percent' => 20,
                'extra_child_third_party_commission_percent' => 20,
                'extra_junior_third_party_commission_percent' => 20,
            ],
            'Single HSBC ENTERTAINER soleil'
            => [
                'price_third_party_commission_percent' => 20,
                'extra_child_third_party_commission_percent' => 20,
                'extra_junior_third_party_commission_percent' => 20,
            ],
            'Family HSBC ENTERTAINER soleil'
            => [
                'price_third_party_commission_percent' => 20,
                'extra_child_third_party_commission_percent' => 20,
                'extra_junior_third_party_commission_percent' => 20,
            ],
        ];

        foreach ($plans as $planName => $plan) {
            DB::table('plans')
                ->where('title', $planName)
                ->update($plan);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(
                'price_third_party_commission_percent',
                'extra_child_third_party_commission_percent',
                'extra_junior_third_party_commission_percent'
            );
        });
    }
};
