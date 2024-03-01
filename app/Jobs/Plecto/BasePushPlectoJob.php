<?php

namespace App\Jobs\Plecto;

use App\Models\BackofficeUser;
use App\Services\PlectoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

abstract class BasePushPlectoJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    protected ?array $ids;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model|array $model = [])
    {
        $this->ids = match (true) {
            is_array($model) => $model,
            $model instanceof BackofficeUser => [$model->id],
            default => null,
        };
    }

    abstract public function handle(PlectoService $plectoService);
}
