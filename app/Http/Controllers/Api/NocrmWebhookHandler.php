<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Nocrm\AnyUpdateWebhookNocrmJob;
use App\Jobs\Nocrm\CommentedWebhookNocrmJob;
use App\Models\Lead\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Queue\InvalidPayloadException;
use Illuminate\Support\Facades\Cache;

class NocrmWebhookHandler extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'webhook_event' => 'required|array',
            'webhook_event.event' => 'in:lead.any_update,lead.deleted,lead.commented',
        ]);

        return match ($request->input('webhook_event.event')) {
            'lead.any_update' => $this->anyUpdate($request),
            'lead.deleted' => $this->deleted($request),
            'lead.commented' => $this->commented($request),
            default => response()->json([
                'message' => 'Event is not supported',
            ]),
        };
    }

    public function anyUpdate(Request $request): JsonResponse
    {
        try {
            $data = $request->webhook_event['data'];
            Cache::set('nocrm_any_data_'.$data['id'], $data, 120);
            AnyUpdateWebhookNocrmJob::dispatch($request->webhook_event['data']);
        } catch (InvalidPayloadException $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Lead has been created or updated',
        ]);
    }

    public function deleted(Request $request): JsonResponse
    {
        $data = $request->webhook_event['data'];

        try {
            Lead::firstWhere('nocrm_id', $data['id'])->delete();
        } catch (\Exception $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Lead has been deleted',
        ]);
    }

    public function commented(Request $request): JsonResponse
    {
        try {
            CommentedWebhookNocrmJob::dispatch($request->webhook_event['data']);
        } catch (InvalidPayloadException $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Lead has been created or updated',
        ]);
    }
}
