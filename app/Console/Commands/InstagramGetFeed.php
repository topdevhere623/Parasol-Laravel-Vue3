<?php

namespace App\Console\Commands;

use App\Services\InstagramFeedService;
use Illuminate\Console\Command;

class InstagramGetFeed extends Command
{
    protected $signature = 'instagram:get-feed';

    protected $description = 'Get and cache instagram feed';

    public function handle(InstagramFeedService $instagramFeedService): int
    {
        $feed = $instagramFeedService->getFeed();

        if (!$feed) {
            return self::FAILURE;
        }

        \Cache::put('homeInstagramFeed', $feed, now()->addWeek());

        return self::SUCCESS;
    }
}
