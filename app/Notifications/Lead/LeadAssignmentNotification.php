<?php

namespace App\Notifications\Lead;

use App\Models\Lead\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeadAssignmentNotification extends Notification
{
    use Queueable;

    public function __construct(private Lead $model)
    {
        //
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $assignerFullName = optional(auth()->user())->full_name ?? 'System';
        return [
            'message' => "{$assignerFullName} has assigned Lead {$this->model->full_name} to you",
            'data' => [
                'userId' => auth()->id(),
                'fullName' => $assignerFullName,
                'leadId' => $this->model->id,
                'leadFullName' => $this->model->full_name,
            ],
        ];
    }
}
