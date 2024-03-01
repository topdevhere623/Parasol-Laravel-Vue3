<?php

namespace App\ParasolCRM\Resources;

use App\Models\BackofficeUser;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadDuplicate;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Fields\DateTime;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Text;
use ParasolCRM\Fields\Textarea;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\ResourceScheme;

class LeadDuplicateResource extends ResourceScheme
{
    public static $model = LeadDuplicate::class;

    protected const STATUS_BADGES = [
        'potential_duplicate' => 'orange',
        'not_duplicate' => 'green',
        'duplicate' => 'red',
    ];

    public function query(Builder $query)
    {
        //                $query->leftJoin('backoffice_users', 'leads.backoffice_user_id', 'backoffice_users.id')
        //                    ->with('backofficeUser');
        $query->selectRaw('leads.nocrm_id, duplicate_lead.nocrm_id as duplicate_nocrm_id')
            ->leftJoin('leads', 'leads.id', 'lead_duplicates.lead_id')
            ->leftJoin('leads as duplicate_lead', 'duplicate_lead.id', 'lead_duplicates.duplicate_lead_id');
    }

    public function fields(): array
    {
        return [
            HorizontalRadioButton::make('status')
                ->options(LeadDuplicate::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            Text::make('nocrm_id', 'Nocrm link')
                ->displayHandler(fn (LeadDuplicate $record) => "https://adv.nocrm.io/leads/{$record->nocrm_id}")
                ->url('https://adv.nocrm.io/leads/{nocrm_id}')
                ->onlyOnTable()
                ->sortable('leads.nocrm_id'),
            Text::make('duplicate_nocrm_id', 'Duplicate Nocrm link')
                ->displayHandler(
                    fn (LeadDuplicate $record) => "https://adv.nocrm.io/leads/{$record->duplicate_nocrm_id}"
                )
                ->onlyOnTable()
                ->url('https://adv.nocrm.io/leads/{duplicate_nocrm_id}')
                ->sortable('duplicate_lead.nocrm_id'),

            Textarea::make('note')
                ->onlyOnForm(),
            //            Text::make('backoffice_user_name', 'Sales Person')
            //                ->displayHandler(function (Lead $record) {
            //                    return $record->backofficeUser?->full_name;
            //                })
            //                ->onlyOnTable()
            //                ->sortable('backoffice_users.name'),
            DateTime::make('created_at')
                ->onlyOnTable()
                ->hideOnTable()
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            InFilter::make(
                (new MultipleSelectFilterField())
                    ->options(LeadDuplicate::getConstOptions('statuses')),
                'status',
                'lead_duplicates.status'
            )->quick(),
            EqualFilter::make(TextFilterField::make(), 'nocrm_id', 'leads.nocrm_id', 'Nocrm ID')
                ->quick(),
            EqualFilter::make(
                TextFilterField::make(),
                'duplicate_nocrm_id',
                'duplicate_lead.nocrm_id',
                'Duplicate Nocrm ID'
            )
                ->quick(),
        ];
    }
}
