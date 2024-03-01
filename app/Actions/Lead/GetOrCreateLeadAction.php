<?php

namespace App\Actions\Lead;

use App\Models\Lead\CrmStep;
use App\Models\Lead\Lead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class GetOrCreateLeadAction
{
    public function handle(
        string|array $email,
        string $firstName,
        string $lastName,
        ?string $phone,
        ?int $ownerId = null
    ): Lead {
        $phone = Str::onlyNumbers($phone);
        $phoneCheck = Str::of($phone);

        $email = is_array($email) ? $email : [$email];

        $lead = Lead::whereIn('email', $email)
            ->when($phone, fn ($query) => $query->orWhere(
                fn ($query) => $query->where('phone', $phone)
                    ->orWhere('phone', 'LIKE', "%{$phone}")
                    ->when(
                        $phoneCheck->startsWith('971'),
                        fn (Builder $query) => $query->orWhere('phone', $phoneCheck->after('971')->toString())
                    )
            ))
            ->latest()
            ->first();

        if (!$lead
            || ($lead->status == Lead::STATUSES['won'] && optional($lead->closed_at)->isBefore(now()->subDays(7)))
        ) {
            $lead = Lead::create(
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email[0],
                    'phone' => $phone,
                    'backoffice_user_id' => $lead?->backoffice_user_id ?? $ownerId,
                    'crm_step_id' => CrmStep::DEFAULT_B2C_STEP,
                    'status' => Lead::STATUSES['todo'],
                ]
            );
        }

        $lead->save();

        return $lead;
    }
}
