<?php

namespace App\Actions\Lead;

use App\Models\BackofficeUser;
use App\Models\Lead\CrmActivity;
use App\Models\Lead\CrmComment;
use App\Models\Lead\Lead;
use App\Services\NocrmService;
use Carbon\Carbon;

class GetCommentsFromNocrmLeadAction
{
    public function handle(Lead $lead): void
    {
        $nocrmService = app(NocrmService::class);

        if (!$nocrmService->isAvailable()) {
            return;
        }

        foreach ($nocrmService->get("leads/{$lead->nocrm_id}/comments") as $item) {
            /** @var CrmComment $crmComment */
            $crmComment = $lead->crmComments()->firstOrNew(
                [
                    'nocrm_id' => $item['id'],
                ],
                [
                    'uuid' => \Str::orderedUuid()->toString(),
                ]
            );

            $crmComment->fill(
                [
                    'backoffice_user_id' => isset($item['user']['id'])
                        ? optional(BackofficeUser::where('nocrm_id', $item['user']['id'])->first())->id
                        : null,
                    'crm_activity_id' => $item['activity_id']
                        ? optional(CrmActivity::where('nocrm_id', $item['activity_id'])->first())->id
                        : null,
                    'is_pinned' => (bool)$item['is_pinned'],
                    'content' => $item['content'] ? trim($item['content'], " \n ") : null,
                    'raw_content' => $item['raw_content'] ? trim($item['raw_content'], " \n ") : null,
                    'extended_info' => $item['extended_info'] ?? null,
                    'created_at' => Carbon::parse($item['created_at'])->setTimezone(config('app.timezone')),
                ]
            );

            $crmComment->saveQuietly();

            if (count($item['attachments']) > 0) {
                foreach ($item['attachments'] as $a_item) {
                    $this->syncAttachment($crmComment, $a_item);
                }
            }

            if (!empty($item['action_item']['email'])) {
                $emailItem = $item['action_item']['email'];
                $crmEmail = $crmComment->actionItem()->firstOrNew(
                    [
                        'nocrm_id' => $emailItem['id'],
                    ],
                    [
                        'uuid' => \Str::orderedUuid()->toString(),
                        'to' => $emailItem['to'],
                        'from' => $emailItem['from'],
                        'from_name' => $emailItem['from_name'] ?? null,
                        'cc' => $emailItem['cc'] ?? null,
                        'bcc' => $emailItem['bcc'] ?? null,
                        'subject' => $emailItem['subject'] ?? null,
                        'content' => $emailItem['content'],
                        'threaded_content' => $emailItem['threaded_content'],
                        'has_more_content' => (bool)($emailItem['has_more_content'] ?? null),
                        'is_read' => (bool)($emailItem['is_read'] ?? null),
                        'status' => $emailItem['status'] ?? null,
                        'scheduled_at' => $emailItem['scheduled_at'] ? Carbon::parse($emailItem['scheduled_at'])->setTimezone(config('app.timezone')) : null,
                        'sent_at' => $emailItem['sent_at'] ? Carbon::parse($emailItem['sent_at'])->setTimezone(config('app.timezone')) : null,
                        'lead_id' => $lead->id,
                        'backoffice_user_id' => optional(BackofficeUser::where('nocrm_id', $emailItem['user']['id'])->first())->id,

                        'nocrm_lead_id' => $emailItem['lead_id'],
                        'nocrm_owner_id' => $emailItem['user']['id'] ?? null,
                        'created_at' => Carbon::parse($emailItem['created_at'])->setTimezone(config('app.timezone')),
                    ],
                );

                $crmEmail->saveQuietly();

                if (count($emailItem['attachments']) > 0) {
                    foreach ($emailItem['attachments'] as $a_item) {
                        $this->syncAttachment($crmEmail, $a_item);
                    }
                }
            }
        }
    }

    private function syncAttachment($model, $a_item)
    {
        $model->crmAttachments()->firstOrNew(
            [
                'nocrm_id' => $a_item['id'],
            ],
            [
                'uuid' => \Str::orderedUuid()->toString(),
                'name' => $a_item['name'] ?? null,
                'url' => $a_item['url'] ?? null,
                'permalink' => $a_item['permalink'] ?? null,
                'content_type' => $a_item['content_type'] ?? null,
                'kind' => $a_item['kind'] ?? null,
            ],
        )
            ->saveQuietly();
    }
}
