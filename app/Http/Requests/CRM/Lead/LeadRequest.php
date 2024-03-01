<?php

namespace App\Http\Requests\CRM\Lead;

use App\Models\Lead\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'first_name' => 'string|nullable',
            'last_name' => 'string|nullable',
            'email' => 'string|nullable|email',
            'phone' => 'string|nullable',
            'amount' => 'numeric|nullable',
            'status' => Rule::in(array_values(Lead::STATUSES)),
            'crm_step_id' => 'int',
            'tag_ids' => 'array',
            'tag_ids.*' => 'integer',
            'backoffice_user_id' => 'int|nullable',
        ];

        if ($this->has('additional')) {
            $rules = array_merge(
                $rules,
                [
                    // доп инфа с комментами из выпадашки, на канбане с лидами
                    'additional.date' => [
                        'required_if:status,'.Lead::STATUSES['standby'],
                        function ($attribute, $value, $fail) {
                            if ($value) {
                                $datePart = optional(\DateTime::createFromFormat('d/m/Y', $value))->format('Y-m-d');
                                if (!$datePart) {
                                    $fail("{$attribute} does not match the format d/m/Y.");
                                }

                                $timePart = $this->input('additional.time');

                                // Validate time only if it exists
                                if ($timePart !== null) {
                                    $combinedDateTime = "{$datePart} {$timePart}";

                                    $date = \DateTime::createFromFormat('Y-m-d H:i', $combinedDateTime);
                                    $now = now();

                                    if ($date <= $now) {
                                        $fail("{$attribute} must be in the future.");
                                    }
                                } else {
                                    // If time doesn't exist, compare only dates
                                    $now = now()->format('Y-m-d');
                                    if ($datePart <= $now) {
                                        $fail("{$attribute} must be in the future.");
                                    }
                                }
                            }
                        },
                    ],
                    'additional.time' => 'nullable|date_format:H:i',
                    'additional.duration' => 'nullable|integer',
                    'additional.cost' => 'nullable|numeric',
                    'additional.comment_todo' => 'nullable|string',
                    'additional.comment_what_have' => 'nullable|string',
                    'additional.activity_id' => 'nullable|integer',
                    'additional.log_activity_id' => 'nullable|integer',
                ]
            );
        }

        return $rules;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->filled('additional.date') && $this->input(
                'status'
            ) == Lead::STATUSES['todo'] ? Lead::STATUSES['standby'] : $this->input('status'),
        ]);
    }

    public function attributes(): array
    {
        return [
            'additional.date' => 'date',
            'additional.time' => 'time',
            'additional.duration' => 'duration',
            'additional.cost' => 'cost',
            'additional.comment_todo' => 'comment_todo',
            'additional.comment_what_have' => 'comment_what_have',
            'additional.activity_id' => 'activity_id',
            'additional.log_activity_id' => 'log_activity_id',
        ];
    }
}
