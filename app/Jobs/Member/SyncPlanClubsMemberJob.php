<?php

namespace App\Jobs\Member;

use App\Actions\Member\SyncAllAvailableClubsPlanMemberAction;
use App\Models\Club\Club;
use App\Models\Member\Member;
use App\Models\Plan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncPlanClubsMemberJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $id;
    protected string $class;

    public $uniqueFor = 4;

    public function __construct(Club|Plan $model)
    {
        $this->id = $model->id;
        $this->class = get_class($model);
    }

    public function uniqueId()
    {
        return self::class.$this->class.$this->id;
    }

    public function handle()
    {
        Plan::when($this->class == Club::class, fn ($query) => $query->whereNotExists(
            fn ($query) => $query->from('plan_club')
                ->where('plan_club.club_id', $this->id)
                ->where('plan_club.type', Plan::PLAN_CLUB_TYPES['exclude'])
                ->whereRaw('plan_club.plan_id = plans.id')
        ))
            ->when($this->class == Plan::class, fn ($query) => $query->where('plans.id', $this->id))
            ->where('plans.allowed_club_type', Plan::ALLOWED_CLUB_TYPES['all_available'])
            ->chunk(100, function (Collection $collection) {
                $collection->each(function (Plan $plan) {
                    Member::active()
                        ->where('plan_id', $plan->id)
                        ->with('plan')
                        ->select('id', 'plan_id')
                        ->chunkById(100, function (Collection $collection) {
                            $collection->each(function (Member $member) {
                                (new SyncAllAvailableClubsPlanMemberAction())->handle($member);
                            });
                        });
                });
            });
    }
}
