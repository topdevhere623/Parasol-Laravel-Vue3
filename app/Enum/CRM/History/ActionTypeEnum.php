<?php

namespace App\Enum\CRM\History;

enum ActionTypeEnum: string
{
    case ClientAssigned = 'client_assigned';
    case StepChanged = 'step_changed';
    case StatusChanged = 'status_changed';
    case AmountSet = 'amount_set';
    case UserAssigned = 'user_assigned';
    case LeadCreated = 'lead_created';
    case CommentAdded = 'comment_added';
    case EmailSent = 'email_sent';
    case LeadEdited = 'lead_edited';
}
