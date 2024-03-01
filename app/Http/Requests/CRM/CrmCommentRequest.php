<?php

namespace App\Http\Requests\CRM;

use App\Models\Lead\CrmActivity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrmCommentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'crm_activity_id' => 'nullable|exists:'.CrmActivity::getTableName().',id',
            'content' => [
                Rule::requiredIf(empty($this->crm_activity_id)),
            ],
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|max:5120',
        ];
    }
}
