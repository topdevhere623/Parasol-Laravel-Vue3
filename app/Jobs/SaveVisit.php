<?php

namespace App\Jobs;

use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveVisit implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        if ($visit = Visit::where('visitable_type', $this->data['visitable_type'])
            ->where('visitable_id', $this->data['visitable_id'])
            ->first()
        ) {
            if (Carbon::parse($visit->updated_at)->diff(Carbon::now())->h > 4) {
                $visit->increment('count');
            }
            return;
        }
        Visit::create($this->data);
    }
}
