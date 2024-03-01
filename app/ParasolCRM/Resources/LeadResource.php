<?php

namespace App\ParasolCRM\Resources;

use App\Models\BackofficeUser;
use App\Models\Booking;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadTag;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Fields\BelongsToMany;
use ParasolCRM\Fields\DateTime;
use ParasolCRM\Fields\Email;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Phone;
use ParasolCRM\Fields\Select;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\BetweenFilter;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\DateFilterField;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

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
                    fn ($record) => \Prsl::checkGatePolicy(
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
            LikeFilter::make(TextFilterField::make(), 'first_name', 'leads.first_name')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'last_name', 'leads.last_name')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'email', 'leads.email'),
            LikeFilter::make(TextFilterField::make(), 'phone', 'leads.phone'),

            EqualFilter::make(TextFilterField::make(), 'nocrm_id', 'leads.nocrm_id'),

            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(BackofficeUser::getSelectable()),
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
                    ->options(Lead::getConstOptions('steps')),
                'step',
                'leads.step'
            )->quick(),

            BetweenFilter::make(DateFilterField::make(), DateFilterField::make(), 'created_at', 'leads.created_at'),
            BetweenFilter::make(DateFilterField::make(), DateFilterField::make(), 'closed_at', 'leads.closed_at'),

            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(LeadTag::getSelectable()),
                'tags',
                'lead_lead_tag.lead_tag_id',
                'Lead tags'
            ),
        ];
    }
}
