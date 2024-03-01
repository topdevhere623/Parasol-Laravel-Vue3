<?php

namespace App\Observers;

use App\Jobs\Member\SyncPlanClubsMemberJob;
use App\Jobs\Passkit\PasskitUpdateMemberLiveClubs;
use App\Models\Club\Club;
use App\Models\Program;
use Illuminate\Support\Str;

class ClubObserver
{
    public function creating(Club $club)
    {
        $club->uuid = (string)Str::orderedUuid();
    }

    public function created(Club $model)
    {
        if (!$model->old_id) {
            $model->old_id = $model->id;
            $model->save();
        }

        // TODO: refactor this
        \DB::table('programs_to_clubs_sort')->insert([
            [
                'program_id' => Program::ADV_PLUS_ID,
                'club_id' => $model->id,
                'sort' => \DB::table('programs_to_clubs_sort')
                    ->where('program_id', Program::ADV_PLUS_ID)
                    ->max('sort') + 1,
            ],
            [
                'program_id' => Program::ENTERTAINER_HSBC,
                'club_id' => $model->id,
                'sort' => \DB::table('programs_to_clubs_sort')
                    ->where('program_id', Program::ENTERTAINER_HSBC)
                    ->max('sort') + 1,
            ],
            [
                'program_id' => Program::ENTERTAINER_SOLEIL_ID,
                'club_id' => $model->id,
                'sort' => \DB::table('programs_to_clubs_sort')
                    ->where('program_id', Program::ENTERTAINER_SOLEIL_ID)
                    ->max('sort') + 1,
            ],
        ]);
    }

    public function saving(Club $model)
    {
        $model->updateTrafficBySlots();
        if ($model->is_always_red) {
            $model->traffic = Club::TRAFFICS['red'];
        }
        $model->slug = Str::slugExtended($model->slug ?: $model->title);
    }

    public function saved(Club $model): void
    {
        if ($model->isDirty(['traffic'])) {
            PasskitUpdateMemberLiveClubs::dispatch($model);
        }

        $originalStatus = $model->getOriginal('status');
        if ($model->isDirty(['is_visible_plan', 'status'])
            && ($originalStatus == Club::STATUSES['active'] || $model->status == Club::STATUSES['active'])
        ) {
            // SyncPlanClubsMemberJob::dispatch($model);
        }
    }
}
