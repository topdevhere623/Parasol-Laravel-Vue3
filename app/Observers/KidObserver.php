<?php

namespace App\Observers;

use App\Jobs\Passkit\PasskitUpdateMember;
use App\Models\Member\Kid;
use Illuminate\Support\Str;

class KidObserver
{
    public function saving(Kid $kid): void
    {
        $kid->first_name = Str::title($kid->first_name);
        $kid->last_name = Str::title($kid->last_name);
    }

    public function saved(Kid $kid): void
    {
        $this->updateParentsPasskit($kid);
    }

    public function deleted(Kid $kid): void
    {
        $this->updateParentsPasskit($kid);
    }

    protected function updateParentsPasskit(Kid $kid)
    {
        foreach ($kid->getParents() as $parent) {
            PasskitUpdateMember::dispatch($parent);
        }
    }
}
