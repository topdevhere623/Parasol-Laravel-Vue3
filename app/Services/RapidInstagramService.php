<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class RapidInstagramService
{
    private PendingRequest $client;

    public function __construct(private string $apiKey)
    {
        $this->client = Http::baseUrl('https://instagram28.p.rapidapi.com')
            ->acceptJson()
            ->asJson()
            ->timeout(60)
            ->retry(3, 50)
            ->withHeaders([
                'X-RapidAPI-Key' => $apiKey,
                'X-RapidAPI-Host' => 'instagram28.p.rapidapi.com',
            ])
            ->withOptions([
                'debug' => config('app.debug') && app()->runningInConsole(),
            ]);
    }

    public function getFeed(): array
    {
        $resultPhotos = [];
        try {
            //            $feed = $this->client->get('media', [
            //                'user_id' => '28387267031',
            //                'batch_size' => 20
            //            ])->object();

            $feed = json_decode(file_get_contents('aa.json'));
            $data = $feed?->data?->user?->edge_owner_to_timeline_media?->edges;

            if ($data) {
                foreach ($data as $feedItem) {
                    $resultPhotos[] = [
                        'url' => 'https://instagram.com/p/'.$feedItem->node->shortcode,
                        'thumbnailUrl' => $feedItem->node->thumbnail_resources[3]->src,
                    ];
                }
            }
        } catch (\Exception $exception) {
            //
        }

        return $resultPhotos;
    }
}
