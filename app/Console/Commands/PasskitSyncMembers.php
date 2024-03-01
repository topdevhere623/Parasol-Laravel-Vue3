<?php

namespace App\Console\Commands;

use App\Jobs\Passkit\PasskitUpdateMember;
use App\Models\Member\Member;
use Illuminate\Console\Command;

class PasskitSyncMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passkit:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public $i = 0;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Member::with('program')->active()->chunk(100, function ($members) {
            $members->each(function (Member $member) {
                if ($member->hasPasskitAccess()) {
                    $this->i++;
                    PasskitUpdateMember::dispatch($member, true);
                }
            });
        });

        echo 'Member dispatched to sync: '.$this->i;
        echo PHP_EOL;

        return Command::SUCCESS;
    }
}
