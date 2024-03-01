<?php

namespace App\Jobs\Nocrm;

use App\Models\BackofficeUser;
use App\Models\Lead\CrmActivity;
use App\Models\Lead\CrmStep;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadTag;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AnyUpdateWebhookNocrmJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 2;

    public $maxExceptions = 3;

    public $uniqueFor = 15;

    protected int $norcmId;

    public function __construct(protected array $data)
    {
        $this->onQueue('high')->delay(now()->addSeconds(15));
        $this->norcmId = $data['id'];
    }

    public function uniqueId()
    {
        return $this->data['id'];
    }

    public function handle()
    {
        $data = Cache::get('nocrm_any_data_'.$this->data['id']);

        $remoteLead = collect($data['extended_info']['fields_by_name'])
            ->mapWithKeys(function ($value, $key) {
                return [Str::of($key)->snake()->trim()->toString() => $value];
            })->toArray();

        $nocrm_owner_id = $data['user']['id'] ?? $data['user_id'];
        $step = Str::of($data['step'])->lower()->snake()->toString();

        $crmStep = CrmStep::where('nocrm_id', $data['step_id'])
            ->first();

        $backofficeUser = BackofficeUser::where('nocrm_id', $nocrm_owner_id)
            ->first();

        $leadData = [
            'nocrm_owner_id' => $nocrm_owner_id,
            'backoffice_user_id' => $backofficeUser?->id,
            'first_name' => trim(
                ($remoteLead['name'] ?? null).' '.($remoteLead['first_name'] ?? null)
            ),
            'last_name' => !empty($remoteLead['last_name']) ? $remoteLead['last_name'] : null,
            'title' => $data['title'] ?? null,
            'phone' => $remoteLead['mobile'] ?? null,
            'email' => $remoteLead['email'] ?? null,
            'status' => strtolower($data['status']),
            'step' => $step,
            'crm_step_id' => $crmStep->id,
            'remind_date' => $data['remind_date'] ?? null,
            'remind_time' => $data['remind_time'] ?? null,
            'reminder_at' => $data['reminder_at'] ? Carbon::parse($data['reminder_at'])->setTimezone(
                config('app.timezone')
            ) : null,
            'reminder_duration' => $data['reminder_duration'] ?? null,
            'reminder_activity_id' => isset($data['reminder_activity_id'])
                ? optional(CrmActivity::where('nocrm_id', $data['reminder_activity_id'])->first())->id
                : null,
            'reminder_note' => $data['reminder_note'] ?? null,
            'created_at' => Carbon::parse($data['created_at'])->setTimezone(config('app.timezone')),
            'updated_at' => Carbon::parse($data['updated_at'])->setTimezone(config('app.timezone')),
            'closed_at' => Carbon::parse($data['closed_at'])->setTimezone(config('app.timezone')),
        ];

        $lead = Lead::firstOrCreate(
            [
                'nocrm_id' => $data['id'],
            ],
            $leadData
        );

        if (!$lead->wasRecentlyCreated) {
            $lead->update($leadData);
        }

        $lead->leadTags()->sync(
            LeadTag::getOrCreate($data['tags'])->pluck('id')
        );
    }

}
