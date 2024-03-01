<?php

namespace App\Console\Commands\Temp;

use App\Models\HSBCUsedCard;
use Illuminate\Console\Command;

class UpdateHsbcUsedCardMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:hsbc-used-cards-members';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        HSBCUsedCard::with('booking.member')->each(function (HSBCUsedCard $card) {
            if ($card->booking->member) {
                $card->member()->associate($card->booking->member)->save();
            }
        });
        return Command::SUCCESS;
    }
}
