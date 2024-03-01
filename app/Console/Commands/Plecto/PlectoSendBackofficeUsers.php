<?php

namespace App\Console\Commands\Plecto;

use App\Jobs\Plecto\PushBackofficeUserPlectoJob;
use App\Models\BackofficeUser;
use Illuminate\Console\Command;

class PlectoSendBackofficeUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plecto:push-backoffice-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push backoffice users data to plecto';

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
        BackofficeUser::chunk(100, function ($collection) {
            $ids = [];
            $collection->each(function ($booking) use (&$ids) {
                $ids[] = $booking->id;
            });

            PushBackofficeUserPlectoJob::dispatch($ids);
        });

        return Command::SUCCESS;
    }
}
