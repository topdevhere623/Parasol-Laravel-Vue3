<?php

use App\Models\Reports\ReportHSBCMonthlyActiveMember;
use App\Scopes\HSBCComplimentaryPlanScope;
use Carbon\Carbon;
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
        Schema::create('report_hsbc_monthly_active_members', function (Blueprint $table) {
            $table->bigInteger('member_id')
                ->index();
            $table->mediumInteger('month_year')
                ->index();
            $table->timestamps();

            $table->unique(['member_id', 'month_year']);
        });

        for ($i = 2; $i <= 6; $i++) {
            $startDate = new Carbon();
            $startDate->setMonth($i);
            $startDate->endOfDay()->endOfMonth();

            $preQuery = \App\Models\Member\Member::active()
                ->select('id')
                ->where('member_type', \App\Models\Member\Member::MEMBER_TYPES['member'])
                ->where('start_date', '<=', $startDate);

            (new HSBCComplimentaryPlanScope())->apply($preQuery, $preQuery->getModel());

            $preQuery->chunkById(10, function ($members) use ($startDate) {
                foreach ($members as $member) {
                    ReportHSBCMonthlyActiveMember::firstOrCreate(['member_id' => $member->id, 'month_year' => $startDate->format('mY')]);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_hsbc_monthly_active_members');
    }
};
