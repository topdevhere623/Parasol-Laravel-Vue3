<?php

namespace App\Jobs\Passkit;

use App\Services\PasskitService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PasskitDeleteMember implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 2;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 2;

    private string $memberId;

    public function __construct(string $memberId)
    {
        $this->memberId = $memberId;
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return self::class.$this->memberId;
    }

    public function handle(PasskitService $passkitService)
    {
        try {
            $passkitService->deleteMember($this->memberId);
        } catch (\Throwable $e) {
            report(new \Exception('Passkit request failed! Member id: '.$this->memberId, $e->getCode(), $e));
            $this->fail($e);
        }
    }
}
