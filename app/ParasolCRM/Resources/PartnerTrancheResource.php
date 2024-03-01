<?php

namespace App\ParasolCRM\Resources;

use App\Models\Partner\Partner;
use App\Models\Partner\PartnerContract;
use App\Models\Partner\PartnerTranche;
use App\Rules\Partner\PartnerTrancheStatusRule;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Number;
use ParasolCRM\Filters\BetweenFilter;
use ParasolCRM\Filters\Fields\DateFilterField;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\ResourceScheme;

class PartnerTrancheResource extends ResourceScheme
{
    public static $model = PartnerTranche::class;

    public const STATUS_BADGES = [
        'active' => 'green',
        'awaiting_first_visit' => 'blue',
        'inactive' => 'light',
        'expired' => 'red',
    ];

    public function tableQuery(Builder $query)
    {
        $query->select('partner.id as partner_id');
    }

    public function fields(): array
    {
        return [
            HorizontalRadioButton::make('status')
                ->options(PartnerTranche::getConstOptions('statuses'))
                ->default('active')
                ->badges(self::STATUS_BADGES)
                ->rules(['required', new PartnerTrancheStatusRule()])
                ->sortable(),
            BelongsTo::make('partner', Partner::class)
                ->onlyOnTable()
                ->titleField('name')
                ->url('/partners/{partner_id}'),
            BelongsTo::make('partnerContract', PartnerContract::class)
                ->url('/partner-contracts/{partner_contract_id}')
                ->titleField('name')
                ->rules(['required']),
            Number::make('adult_slots')
                ->rules('required')
                ->default(0),
            Number::make('kid_slots')
                ->rules('required')
                ->default(0),
            Number::make('single_membership_count')
                ->rules('required')
                ->default(0),
            Number::make('family_membership_count')
                ->rules('required')
                ->default(0),
            Number::make('individual_kid_membership_count')
                ->rules('required')
                ->default(0),
            Date::make('start_date')
                ->nullable(),
            Date::make('expiry_date')
                ->nullable(),

        ];
    }

    public function layout(): array
    {
        return [
            Group::make('')->attach([
                'status',
                'partnerContract',
                'adult_slots',
                'kid_slots',
                'single_membership_count',
                'family_membership_count',
                'individual_kid_membership_count',
                'start_date',
                'expiry_date',
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            InFilter::make(
                MultipleSelectFilterField::make()->options(PartnerTranche::getConstOptions('statuses')),
                'status',
                'partner_tranches.status'
            )
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()->options(Partner::oldest('name')->pluck('name', 'id')->toArray()),
                'partner',
                'partner.id'
            )
                ->quick(),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'start_date',
                'partner_tranches.start_date'
            )
                ->quick(),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'expiry_date',
                'partner_tranches.expiry_date'
            )
                ->quick(),
        ];
    }
}
