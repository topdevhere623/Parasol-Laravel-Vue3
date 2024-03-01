<?php

namespace App\ParasolCRM\Resources;

use App\Models\Partner\Partner;
use App\Models\Partner\PartnerContract;
use App\Models\Partner\PartnerContractFile;
use App\Rules\Partner\PartnerContractTypeRule;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\Boolean;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\File;
use ParasolCRM\Fields\HasMany;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Money;
use ParasolCRM\Fields\Number;
use ParasolCRM\Fields\Select;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\BetweenFilter;
use ParasolCRM\Filters\Fields\DateFilterField;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class PartnerContractResource extends ResourceScheme
{
    public static $model = PartnerContract::class;

    public const STATUS_BADGES = [
        'active' => 'green',
        'pending' => 'yellow',
        'inactive' => 'light',
        'expired' => 'red',
    ];

    public const TYPE_BADGES = [
        'addendum' => 'yellow',
        'first_year' => 'green',
        'renewal' => 'blue',
    ];
    public const ACCESS_TYPE_BADGES = [
        'prepaid' => 'green',
        'postpaid' => 'blue',
    ];

    public function fields(): array
    {
        return [
            Text::make('name')
                ->rules('required')
                ->sortable(),

            HorizontalRadioButton::make('status')
                ->options(PartnerContract::getConstOptions('statuses'))
                ->default('active')
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),

            HorizontalRadioButton::make('type')
                ->options(PartnerContract::getConstOptions('types'))
                ->default(PartnerContract::TYPES['first_year'])
                ->badges(self::TYPE_BADGES)
                ->rules([
                    'required',
                    new PartnerContractTypeRule(),
                ])
                ->sortable(),

            Select::make('billing_period')
                ->options(PartnerContract::getBillingPeriodOptions())
                ->default(PartnerContract::yearToBillingPeriod(date('Y')))
                ->sortable()
                ->rules('required'),

            BelongsTo::make('partner', Partner::class)
                ->titleField('name')
                ->rules('required')
                ->url('/partners/{partner_id}'),

            BelongsTo::make('parent', PartnerContract::class)
                ->titleField('name')
                ->dependsOn('type', PartnerContract::TYPES['addendum'])
                ->url('/partner_contracts/{parent_id}'),

            Date::make('start_date')
                ->rules('required')
                ->default(today())
                ->nullable(),

            Date::make('expiry_date')
                ->rules('required')
                ->nullable(),

            HorizontalRadioButton::make('access_type')
                ->options(PartnerContract::getConstOptions('access_types'))
                ->default(PartnerContract::ACCESS_TYPES['prepaid'])
                ->badges(self::ACCESS_TYPE_BADGES)
                ->rules(['required'])
                ->sortable(),

            Money::make('single_membership_price')
                ->default(0)
                ->dependsOn('access_type', PartnerContract::ACCESS_TYPES['postpaid'], ['hide', 'set' => 0])
                ->rules(['required']),

            Money::make('family_membership_price')
                ->default(0)
                ->dependsOn('access_type', PartnerContract::ACCESS_TYPES['postpaid'], ['hide', 'set' => 0])
                ->rules(['required']),

            Money::make('adult_cost_per_visit')
                ->default(0)
                ->dependsOn('access_type', PartnerContract::ACCESS_TYPES['prepaid'], ['hide', 'set' => 0])
                ->rules(['required']),

            Money::make('kid_cost_per_visit')
                ->default(0)
                ->dependsOn('access_type', PartnerContract::ACCESS_TYPES['prepaid'], ['hide', 'set' => 0])
                ->rules(['required']),

            Money::make('individual_kid_membership_price')
                ->default(0)
                ->rules(['required']),

            Number::make('classes_slots')
                ->default(0)
                ->rules(['required']),

            Number::make('single_membership_kids_per_slot')
                ->default(1)
                ->rules(['required']),

            Number::make('family_membership_adults_per_slot')
                ->default(1)
                ->rules(['required']),

            Number::make('family_membership_kids_per_slot')
                ->default(1)
                ->rules(['required']),

            Boolean::make('monthly_prepaid_checkin_slots_limit_show', 'Monthly prepaid checkin slots limit')
                ->computed()
                ->unfillableRecord()
                ->setFromRecordHandler(
                    fn (PartnerContract $contract) => $contract->monthly_prepaid_checkin_slots_limit > 0
                ),
            Number::make('monthly_prepaid_checkin_slots_limit')
                ->dependsOn('monthly_prepaid_checkin_slots_limit_show', false, ['hide', 'set' => 0])
                ->setValueHandler(
                    fn ($value, $field) => filter_var(
                        request('monthly_prepaid_checkin_slots_limit_show'),
                        FILTER_VALIDATE_BOOLEAN
                    ) ? $value : 0
                )
                ->default(0)
                ->rules(['required_if:monthly_prepaid_checkin_slots_limit,true']),

            Boolean::make('monthly_over_limit_fee_show', 'Monthly over limit fee')
                ->computed()
                ->unfillableRecord()
                ->dependsOn('monthly_prepaid_checkin_slots_limit_show', false, ['hide', 'set' => false])
                ->setFromRecordHandler(
                    fn (PartnerContract $contract) => $contract->monthly_prepaid_checkin_slots_limit > 0
                        && ($contract->monthly_over_limit_adult_fee > 0 || $contract->monthly_over_limit_kid_fee > 0)
                ),

            Number::make('monthly_over_limit_adult_fee')
                ->dependsOn('monthly_over_limit_fee_show', false, ['hide', 'set' => 0])
                ->setValueHandler(
                    fn ($value, $field) => filter_var(
                        request('monthly_over_limit_fee_show'),
                        FILTER_VALIDATE_BOOLEAN
                    ) ? $value : 0
                )
                ->default(0)
                ->rules(['required_if:monthly_over_limit_fee_show,true']),

            Number::make('monthly_over_limit_kid_fee')
                ->dependsOn('monthly_over_limit_fee_show', false, ['hide', 'set' => 0])
                ->setValueHandler(
                    fn ($value, $field) => filter_var(
                        request('monthly_over_limit_fee_show'),
                        FILTER_VALIDATE_BOOLEAN
                    ) ? $value : 0
                )
                ->default(0)
                ->rules(['required_if:monthly_over_limit_fee_show,true']),

            HorizontalRadioButton::make('kids_access_type')
                ->options(PartnerContract::getConstOptions('kids_access_types'))
                ->default(PartnerContract::KIDS_ACCESS_TYPES['linked'])
                ->rules(['required'])
                ->sortable(),

            HorizontalRadioButton::make('slots_type')
                ->options(PartnerContract::getConstOptions('slots_types'))
                ->default(PartnerContract::SLOTS_TYPES['revolving'])
                ->rules(['required'])
                ->sortable(),

            HasMany::make('files', PartnerContractFile::class, 'files', 'Attachment files')
                ->fields([
                    File::make('file')
                        ->rules('required'),
                ])
                ->repeaterTitle('file')
                ->onlyOnForm(),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('')->attach([
                'name',
                'status',
                'type',
                'partner',
                'parent',
                'start_date',
                'expiry_date',
                'billing_period',
                'access_type',
                'single_membership_price',
                'family_membership_price',
                'adult_cost_per_visit',
                'kid_cost_per_visit',
                'individual_kid_membership_price',
                'classes_slots',
                'single_membership_kids_per_slot',
                'family_membership_adults_per_slot',
                'family_membership_kids_per_slot',
                'monthly_prepaid_checkin_slots_limit_show',
                'monthly_prepaid_checkin_slots_limit',
                'monthly_over_limit_fee_show',
                'monthly_over_limit_adult_fee',
                'monthly_over_limit_kid_fee',
                'kids_access_type',
                'slots_type',
            ]),
            Group::make('files')->attach([
                'files',
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'name', 'partner_contracts.name')
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()->options(PartnerContract::getConstOptions('statuses')),
                'status',
                'partner_contracts.status'
            )
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()->options(PartnerContract::getBillingPeriodOptions()),
                'billing_period',
                'partner_contracts.billing_period'
            )
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()->options(PartnerContract::getConstOptions('types')),
                'type',
                'partner_contracts.type'
            ),
            InFilter::make(
                MultipleSelectFilterField::make()->options(
                    Partner::oldest('name')
                        ->pluck('name', 'id')
                        ->toArray()
                ),
                'partner',
                'partner_contracts.partner_id'
            )
                ->quick(),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'start_date',
                'partner_contracts.start_date'
            ),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'expiry_date',
                'partner_contracts.expiry_date'
            )
                ->quick(),
        ];
    }
}
