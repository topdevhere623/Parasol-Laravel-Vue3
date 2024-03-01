<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\BackofficeUserSales;
use App\Models\Booking;
use App\Models\Lead\CrmPipeline;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadTag;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Fields\BelongsToMany;
use ParasolCRMV2\Fields\DateTime;
use ParasolCRMV2\Fields\Email;
use ParasolCRMV2\Fields\HorizontalRadioButton;
use ParasolCRMV2\Fields\Phone;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\BetweenFilter;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\SelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

class LeadResource extends ResourceScheme
{
    public static $model = Lead::class;

    public static $defaultSortBy = 'created_at';

    public static $defaultSortDirection = 'desc';

    protected const STATUS_BADGES = [
        'won' => 'green',
        'todo' => 'red',
        'standby' => 'blue',
        'cancelled' => 'light',
        'lost' => 'gray',
    ];
    protected const STEP_BADGES = [
        'default' => 'light',
    ];

    public function query(Builder $query)
    {
        $query->selectRaw('bookings.id as booking_id, bookings.reference_id')
            ->leftJoin('lead_lead_tag', 'lead_lead_tag.lead_id', '=', 'leads.id')
            ->leftJoin('backoffice_users', 'backoffice_users.id', 'leads.backoffice_user_id')
            ->leftJoin('crm_steps', 'crm_steps.id', 'leads.crm_step_id')
            ->leftJoinSub(Booking::orderBy('step', 'desc'), 'bookings', 'bookings.lead_id', 'leads.id')
            ->with('backofficeUser', 'booking');
    }

    public function fields(): array
    {
        return [
            HorizontalRadioButton::make('status')
                ->options(Lead::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            Select::make('step')
                ->options(Lead::getConstOptions('steps'))
                ->badges(self::STEP_BADGES)
                ->rules('required')
                ->sortable(),
            Text::make('reference_id', 'Booking ID')
                ->url(
                    fn ($record) => \PrslV2::checkGatePolicy(
                        'view',
                        Booking::class,
                        $record
                    ) ? ('/bookings/'.$record->booking_id.'/view') : null
                )
                ->sortable('bookings.reference_id'),
            Text::make('first_name')
                ->onlyOnForm()
                ->rules('required'),
            Text::make('last_name')
                ->onlyOnForm(),
            Text::make('full_name')
                ->onlyOnTable()
                ->sortable('leads.first_name'),
            Email::make('email')
                ->sortable(),
            Phone::make('phone')
                ->sortable(),
            Text::make('nocrm_id', 'Nocrm link')
                ->displayHandler(fn (Lead $record) => "https://adv.nocrm.io/leads/{$record->nocrm_id}")
                ->url('https://adv.nocrm.io/leads/{nocrm_id}')
                ->sortable(),
            Text::make('backoffice_user_name', 'Sales Person')
                ->displayHandler(function (Lead $record) {
                    return $record->backofficeUser?->full_name;
                })
                ->onlyOnTable()
                ->sortable('backoffice_users.name'),
            DateTime::make('created_at')
                ->onlyOnTable()
                ->sortable(),
            BelongsToMany::make('leadTags', LeadTag::class, 'tags')
                ->titleField('name')
                ->onlyOnForm(),
            Text::make('tags')
                ->computed()
                ->displayHandler(function (Lead $record) {
                    return $record->leadTags?->pluck('name')->implode(', ');
                })
                ->onlyOnTable()
                ->hideOnTable(),
        ];
    }

    public function filters(): array
    {
        return [
            EqualFilter::make(
                SelectFilterField::make(CrmPipeline::getSelectableDefaultValue())
                    ->options(CrmPipeline::getSelectable()),
                'pipeline',
                'crm_steps.crm_pipeline_id'
            )
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'first_name', 'leads.first_name')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'last_name', 'leads.last_name')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'email', 'leads.email'),
            LikeFilter::make(TextFilterField::make(), 'phone', 'leads.phone'),

            EqualFilter::make(TextFilterField::make(), 'nocrm_id', 'leads.nocrm_id'),

            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(BackofficeUserSales::getSelectable()),
                'Owner',
                'backoffice_users.id',
            )->quick(),

            InFilter::make(
                (new MultipleSelectFilterField())
                    ->options(Lead::getConstOptions('statuses')),
                'status',
                'leads.status'
            )->quick(),
            InFilter::make(
                (new MultipleSelectFilterField())
                    ->options($this->getPipelineSteps()),
                'step',
                'leads.crm_step_id'
            )->quick(),

            BetweenFilter::make(DateFilterField::make(), DateFilterField::make(), 'created_at', 'leads.created_at'),
            BetweenFilter::make(DateFilterField::make(), DateFilterField::make(), 'closed_at', 'leads.closed_at'),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'remind_date',
                'leads.remind_date',
                'Activity due date'
            ),

            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(LeadTag::getSelectable()),
                'tags',
                'lead_lead_tag.lead_tag_id',
                'Lead tags'
            ),
        ];
    }

    protected function getPipelineSteps(): array
    {
        $data = [];
        CrmPipeline::with(['crmSteps' => fn ($query) => $query->orderBy('position')])
            ->get()
            ->transform(function ($pipeline) use (&$data) {
                $data[$pipeline->id] = [
                    'value' => $pipeline->id,
                    'label' => $pipeline->name,
                    'options' => $pipeline->crmSteps
                        ->transform(fn ($step) => [
                            'value' => $step->id,
                            'label' => $step->name,
                        ])
                        ->toArray(),
                ];
            });

        return $data;
    }
}
