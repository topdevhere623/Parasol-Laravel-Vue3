<?php

namespace App\Actions\ProgramApi;

use App\Models\Program;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class ProgramSendApiWebhookAction
{
    /**
     * @throws RequestException
     * @throws \Throwable
     */
    public function handle(Program $program, string $type, array $data): void
    {
        $webhookUrl = $program->getApiWebhookUrl();
        throw_unless(
            $webhookUrl,
            new \Exception("API Webhook not found for program {$program->id}:{$program->name}")
        );

        Http::withToken($program->api_key)
            ->asJson()
            ->acceptJson()
            ->post(
                $webhookUrl,
                ['type' => $type, 'data' => $data]
            )->throw();
    }
}
