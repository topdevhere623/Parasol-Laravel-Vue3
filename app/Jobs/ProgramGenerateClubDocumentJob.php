<?php

namespace App\Jobs;

use App\Actions\ProgramGenerateClubDocumentAction;
use App\Models\Program;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProgramGenerateClubDocumentJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 120;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 10;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected int $programId)
    {
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return self::class.$this->programId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new ProgramGenerateClubDocumentAction())->handle(Program::find($this->programId));
    }
}
