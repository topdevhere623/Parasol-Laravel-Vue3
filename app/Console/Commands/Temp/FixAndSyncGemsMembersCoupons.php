<?php

namespace App\Console\Commands\Temp;

use App\Jobs\Gems\GemsSendBooking;
use App\Models\Booking;
use Illuminate\Console\Command;

class FixAndSyncGemsMembersCoupons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:gems-sync';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Booking::where('type', 'renewal')->whereHas(
            'plan',
            fn ($q) => $q->whereHas('package', fn ($q) => $q->where('program_id', 7))
        )->where('step', 5)
            ->where('id', '!=', 3368)
            ->each(fn ($b) => GemsSendBooking::dispatchSync($b->id));

        $booking = Booking::find(908);
        $booking->type = 'renewal';
        $booking->save();
        GemsSendBooking::dispatchSync(908);

        $booking = Booking::find(908);
        $booking->type = 'booking';
        $booking->save();

        return Command::SUCCESS;
    }
}
