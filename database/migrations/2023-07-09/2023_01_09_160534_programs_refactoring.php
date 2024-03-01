<?php

use App\Models\Program;
use App\Models\Referral;
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
        Schema::table('programs', function (Blueprint $table) {
            $table->char('referral_code_template', 20)
                ->after('last_seen');
            $table
                ->string('coupon_template', 20)
                ->after('referral_code_template');
            $table->smallInteger('referral_amount')
                ->after('last_seen');
            $table
                ->enum('referral_amount_type', [
                    'percentage',
                    'fixed',
                ])
                ->after('last_seen');
            $table->json('rewards')
                ->after('referral_amount_type')
                ->nullable();
        });
        DB::table('programs')
            ->update([
                'referral_amount' => 10,
                'referral_amount_type' => 'percentage',
                'referral_code_template' => '{10}',
                'rewards' => json_encode(array_keys(Referral::REWARDS)),
            ]);
        Program::where('source', Program::SOURCE_MAP['gems'])
            ->update([
                'referral_amount' => 0,
                'rewards' => json_encode([Referral::REWARDS['additional_month']]),
            ]);
        DB::table('program_plan_referral')->truncate();
        $data = [];
        foreach (
            Program::where('source', '!=', Program::SOURCE_MAP['gems'])
                ->orWhereNull('source')
                ->pluck('id')
                ->toArray() as $programId
        ) {
            $data = array_merge($data, [
                [
                    'program_id' => $programId,
                    'plan_id' => 1,
                    'type' => 'include',
                ],
                [
                    'program_id' => $programId,
                    'plan_id' => 2,
                    'type' => 'include',
                ],
                [
                    'program_id' => $programId,
                    'plan_id' => 3,
                    'type' => 'include',
                ],
            ]);
        }
        DB::table('program_plan_referral')->insert($data);
    }

    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('referral_amount');
            $table->dropColumn('referral_amount_type');
            $table->dropColumn('referral_code_template');
            $table->dropColumn('rewards');
        });
    }
};
