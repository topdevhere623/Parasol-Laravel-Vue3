<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\Lead\LeadRequest;
use App\Http\Resources\CRM\Lead\LeadBackofficeUserResource;
use App\Http\Resources\CRM\Lead\LeadCategoryResource;
use App\Http\Resources\CRM\Lead\LeadDuplicateResource;
use App\Http\Resources\CRM\Lead\LeadPipelineResource;
use App\Http\Resources\CRM\Lead\LeadResource;
use App\Jobs\Nocrm\UpdateLeadNocrmJob;
use App\Models\BackofficeUser;
use App\Models\Lead\CrmPipeline;
use App\Models\Lead\CrmStep;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadCategory;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function view(Lead $lead): JsonResponse
    {
        $lead->load(
            'backofficeUser',
            'booking.payment',
            'leadTags',
            'createdBy',
            'member',
            'crmComments.backofficeUser',
            'crmComments.crmActivity',
            'crmComments.crmAttachments',
            'crmComments.actionItem.crmAttachments',
            'crmHistory.historyable',
            'crmHistory.crmActivity',
            'crmHistory.backofficeUser',
        );

        $data = [
            'model' => LeadResource::make($lead),
            'owners' => LeadBackofficeUserResource::collection(
                BackofficeUser::where(fn ($query) => $query->whereRoleIs('sales')
                    ->orWhere('id', 81))
                    ->orderByRaw('first_name, last_name')
                    ->get()
            ),
            'pipelines' => LeadPipelineResource::collection(
                CrmPipeline::with(['crmSteps' => fn ($query) => $query->orderBy('position')])->get()
            ),
        ];

        return \Prsl::responseData($data);
    }

    public function edit(?Lead $lead): JsonResponse
    {
        $data = [];
        if ($lead->id) {
            $lead->load(
                'backofficeUser',
                'booking.payment',
                'leadTags',
                'createdBy',
                'crmComments.backofficeUser',
                'crmComments.crmActivity',
                'crmComments.crmAttachments',
                'crmComments.actionItem.crmAttachments',
                'crmHistory.historyable',
                'crmHistory.crmActivity',
                'crmHistory.backofficeUser',
            );
        } else {
            $lead->crm_step_id = CrmStep::DEFAULT_B2C_STEP;
            $lead->status = Lead::STATUSES['todo'];
        }

        $data['model'] = LeadResource::make($lead);

        $pipelines = CrmPipeline::with(['crmSteps' => fn ($query) => $query->orderBy('position')])->get();
        $leadCategories = LeadCategory::with('leadTags')->orderBy('name')->get();

        $data = array_merge($data, [
            'owners' => LeadBackofficeUserResource::collection(
                BackofficeUser::where(fn ($query) => $query->whereRoleIs('sales')->orWhere('id', 81))->orderByRaw(
                    'first_name, last_name'
                )->get()
            ),
            'categories' => LeadCategoryResource::collection($leadCategories),
            'pipelines' => LeadPipelineResource::collection($pipelines),
            'statuses' => Lead::getConstOptions('statuses'),
        ]);

        return \Prsl::responseData($data);
    }

    public function update(LeadRequest $request, Lead $lead): JsonResponse
    {
        $attributes = $this->processRequestData($request);
        $lead->update($attributes);

        $lead->leadTags()->sync($request->tag_ids);

        UpdateLeadNocrmJob::dispatch($lead);

        return $this->edit($lead);
    }

    private function processRequestData(Request $request): array
    {
        $attributes = $request->only([
            'first_name',
            'last_name',
            'email',
            'phone',
            'amount',
            'status',
            'crm_step_id',
            'backoffice_user_id',
        ]);

        if ($request->input('status') == Lead::STATUSES['standby']) {
            if ($request->has('additional.date')) {
                $attributes['remind_date'] = $request->input('additional.date') ? Carbon::createFromFormat(
                    'd/m/Y',
                    $request->input('additional.date')
                ) : null;
                $attributes['reminder_activity_log_id'] = null;
            }
            if ($request->has('additional.time')) {
                $attributes['remind_time'] = $request->input('additional.time');
            }
            if ($request->has('additional.duration')) {
                $attributes['reminder_duration'] = $request->input('additional.duration');
            }
            if ($request->has('additional.log_activity_id')) {
                $attributes['reminder_activity_id'] = $request->input('additional.log_activity_id');
            }
            if ($request->has('additional.comment_what_have')) {
                $attributes['reminder_note'] = $request->input('additional.comment_what_have');
            }
            if (!empty($attributes['remind_date'])) {
                $attributes['reminder_at'] = Carbon::create($attributes['remind_date'])->setTimeFromTimeString(
                    $attributes['remind_time'] ?? '00:00'
                );
            }
        } elseif (in_array($request->input('status'), Lead::CLOSED_STATUSES)) {
            $attributes['remind_date'] = null;
            $attributes['remind_time'] = null;
            $attributes['reminder_at'] = null;
            $attributes['reminder_duration'] = null;
            $attributes['reminder_activity_log_id'] = null;
            $attributes['reminder_activity_id'] = null;
        }

        return $attributes;
    }

    public function create(LeadRequest $request): JsonResponse
    {
        $lead = Lead::create(
            $request->only([
                'first_name',
                'last_name',
                'email',
                'phone',
                'status',
                'crm_step_id',
                'backoffice_user_id',
            ])
        );

        $lead->leadTags()->sync($request->tag_ids);
        $lead->refresh();

        UpdateLeadNocrmJob::dispatch($lead);

        return $this->edit($lead);
    }

    public function checkDuplicates(Request $request)
    {
        $id = $request->input('id');
        $fullName = trim($request->input('first_name').' '.$request->input('last_name'));
        $email = $request->input('email');
        $phone = $request->input('phone');

        if ($fullName || $email || $phone) {
            $query = Lead::query()
                ->withoutGlobalScopes()
                ->withoutTrashed()
                ->with(['backofficeUser'])
                ->when($id, fn ($q) => $q->where('id', '!=', $id))
                ->where(
                    fn ($q) => $q->when(
                        $fullName,
                        fn ($q) => $q->where(
                            \DB::raw("TRIM(CONCAT(COALESCE(`first_name`, ''), ' ', COALESCE(`last_name`, '')))"),
                            '=',
                            $fullName
                        )
                    )
                        ->when($email, fn ($q) => $q->orWhere('email', $email))
                        ->when($phone, fn ($q) => $q->orWhere('phone', $phone))
                )
                ->take(20);

            $leads = $query->get();
            foreach ($leads as $lead) {
                $similarities = [];

                if ($fullName && strtolower($lead->full_name) == strtolower($fullName)) {
                    $similarities[] = 'Same full name';
                }
                if ($email && $lead->email == $email) {
                    $similarities[] = 'Same email';
                }
                if ($phone && $lead->phone == $phone) {
                    $similarities[] = 'Same phone';
                }

                $lead->similarities = $similarities;
            }
        }

        return LeadDuplicateResource::collection($leads ?? [])->response();
    }

    public function destroy(Lead $lead): JsonResponse
    {
        $lead->delete();
        return \Prsl::responseSuccess('Lead has been deleted');
    }

    public function deleteNote(Lead $lead): JsonResponse
    {
        $lead->reminder_note = null;
        $lead->save();

        return \Prsl::responseSuccess();
    }

    public function deleteActivity(Lead $lead): JsonResponse
    {
        $lead->reminder_activity_id = null;
        $lead->reminder_activity_log_id = null;
        $lead->save();

        return \Prsl::responseSuccess();
    }
}
