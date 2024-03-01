<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class InstagramFeedService
{
    private string $accessToken;
    private PendingRequest $client;

    public function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
        $this->client = Http::baseUrl('https://graph.instagram.com')
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->retry(3, 50)
            ->withOptions([
                'debug' => config('app.debug') && app()->runningInConsole(),
            ]);
    }

    public function getFeed(): array
    {
        $resultPhotos = [];
        try {
            $feed = $this->client->get('me/media', [
                'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink, thumbnail_resources',
                'access_token' => $this->accessToken,
            ])->object();

            if (property_exists($feed, 'data') && is_array($feed->data)) {
                foreach ($feed->data as $feedItem) {
                    dd($feedItem);
                    $resultPhotos[] = [
                        'url' => $feedItem->permalink,
                        'thumbnailUrl' => $feedItem->media_type == 'VIDEO' ? $feedItem->thumbnail_url : $feedItem->media_url,
                        'caption' => $feedItem->caption,
                    ];
                }
            }
        } catch (\Exception $exception) {
            //
        }

        return $resultPhotos;
    }
}
