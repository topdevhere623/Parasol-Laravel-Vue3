<?php

namespace App\Notifications\Lead;

use App\Models\Lead\CrmComment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeadMentionNotification extends Notification
{
    use Queueable;

    public function __construct(private CrmComment $model)
    {
        //
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $ownerText = match (optional($this->model->commentable)->backoffice_user_id) {
            null => 'unassigned',
            $notifiable->id => 'your',
            $this->model->backoffice_user_id => 'their',
            default => optional($this->model->commentable->backofficeUser)->full_name."'s",
        };

        $assignerFullName = optional($this->model->backofficeUser)->full_name ?? 'System';
        return [
            'message' => "{$assignerFullName} mentioned you on {$ownerText} lead",
            'data' => [
                'userId' => optional($this->model->backofficeUser)->id,
                'fullName' => $assignerFullName,
                'leadId' => $this->model->commentable_id,
                'commentId' => $this->model->id,
                'commentContent' => $this->model->content,
            ],
        ];
    }
}
