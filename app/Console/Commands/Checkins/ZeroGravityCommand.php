<?php

namespace App\Console\Commands\Checkins;

use App\Models\Club\Club;
use Illuminate\Console\Command;

class ZeroGravityCommand extends Command
{
    protected $signature = 'zero-gravity:traffic';

    protected $description = 'Set Zero Gravity | Dubai club traffic red';

    public function handle()
    {
        $zeroGravity = Club::whereTitle('Zero Gravity | Dubai')->first();

        if ($zeroGravity) {
            $zeroGravity->traffic = in_array(date('N'), [1]) ? Club::TRAFFICS['green'] : Club::TRAFFICS['red'];
            $zeroGravity->save();
        }
    }
}
