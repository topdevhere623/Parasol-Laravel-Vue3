<?php

namespace App\Observers\Lead;

use App\Enum\CRM\History\ActionTypeEnum;
use App\Models\BackofficeUser;
use App\Models\Lead\CrmComment;
use App\Notifications\Lead\LeadMentionNotification;
use App\Services\Crm\CrmHistoryService;
use Illuminate\Support\Facades\Notification;

class CrmCommentObserver
{
    public function creating(CrmComment $model): void
    {
        $model->backoffice_user_id ??= auth()->id();

        if ($model->content || $model->raw_content) {
            $model->raw_content ??= $model->content;
            $model->content = $this->stripMentions($model->content ?? $model->raw_content);
        }
    }

    public function created(CrmComment $model): void
    {
        CrmHistoryService::createHistory($model, ActionTypeEnum::CommentAdded);
        $this->notifyMentionable($model);
    }

    public function updating(CrmComment $model): void
    {
        if ($this->stripMentions($model->content) != $this->stripMentions($model->raw_content)) {
            if ($model->isDirty('content')) {
                $model->raw_content = $model->content;
            } else {
                $model->content = $model->raw_content;
            }
        }

        $model->content = $this->stripMentions($model->content);
    }

    public function updated(CrmComment $model): void
    {
        if ($model->isDirty('content')) {
            $this->notifyMentionable($model);
        }
    }

    private function notifyMentionable(CrmComment $model): void
    {
        $mentions = $model->mentions();
        if (
            count($mentions)
            && ($backofficeUsers = BackofficeUser::whereIn('id', array_column($mentions, 'user'))->get())->isNotEmpty()
        ) {
            Notification::send($backofficeUsers, new LeadMentionNotification($model));
        }
    }

    private function stripMentions($value): string
    {
        return preg_replace_callback('/%\{(.*?)}/', function ($matches) {
            $parts = explode(',', $matches[1]);
            if (isset($parts[1])) {
                $nameParts = explode(':', $parts[1]);
                if (isset($nameParts[1])) {
                    return trim(trim($nameParts[1]), '"');
                }
            }
            return $matches[0];
        }, (string)$value);
    }
}
