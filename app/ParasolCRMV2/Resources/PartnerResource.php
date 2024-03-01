<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Club\Club;
use App\Models\Partner\Partner;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Containers\TabElement;
use ParasolCRMV2\Containers\VerticalTab;
use ParasolCRMV2\Fields\BelongsToMany;
use ParasolCRMV2\Fields\Boolean;
use ParasolCRMV2\Fields\Date;
use ParasolCRMV2\Fields\HasMany;
use ParasolCRMV2\Fields\Money;
use ParasolCRMV2\Fields\Number;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Fields\Textarea;
use ParasolCRMV2\Filters\BetweenFilter;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

class PartnerResource extends ResourceScheme
{
    public static $model = Partner::class;

    public static $defaultSortBy = 'name';

    public static $defaultSortDirection = 'ASC';

    public const STATUS_BADGES = [
        'active' => 'green',
        'in_progress' => 'blue',
        'paused' => 'orange',
        'inactive' => 'light',
        'cancelled' => 'red',
        'pipeline' => 'blue',
        'declined' => 'red',
        'default' => 'light',
    ];

    public function tableQuery(Builder $query)
    {
        $query->withCount('clubs');
    }

    public function fields(): array
    {
        return [
            Text::make('name')
                ->rules('required')
                ->withMeta(['nowrap_text' => true])
                ->sortable(),
            Select::make('status')
                ->options(Partner::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            Boolean::make('is_pooled_access', 'Pooled access')
                ->sortable(),
            Number::make('clubs_count', 'Number of linked clubs')
                ->sortable('clubs_count')
                ->onlyOnTable(),
            Number::make('adult_slots')
                ->sortable()
                ->unfillableRecord(),
            Number::make('kid_slots')
                ->sortable()
                ->unfillableRecord(),
            Number::make('purchased_single_membership')
                ->sortable()
                ->unfillableRecord(),
            Money::make('single_membership_price')
                ->sortable()
                ->unfillableRecord(),
            Number::make('purchased_family_membership')
                ->sortable()
                ->unfillableRecord(),
            Money::make('family_membership_price')
                ->sortable()
                ->unfillableRecord(),
            Number::make('purchased_kid_membership')
                ->sortable()
                ->unfillableRecord(),
            Money::make('individual_kid_membership_price')
                ->sortable()
                ->unfillableRecord(),

            Money::make('adult_cost_per_visit')
                ->sortable()
                ->unfillableRecord(),
            Money::make('kid_cost_per_visit')
                ->sortable()
                ->unfillableRecord(),

            Money::make('contract_value')
                ->sortable()
                ->unfillableRecord(),

            Date::make('current_contract_expiry')
                ->sortable()
                ->unfillableRecord(),

            Date::make('tranche_expiry')
                ->unfillableRecord(),

            Number::make('single_membership_forecast_price')
                ->onlyOnForm()
                ->default(0),
            Money::make('family_membership_forecast_price')
                ->onlyOnForm()
                ->default(0),
            Text::make('website')
                ->nullable()
                ->onlyOnForm(),
            Date::make('first_interaction')
                ->default(today())
                ->onlyOnForm()
                ->nullable(),
            Textarea::make('notes')
                ->nullable()
                ->onlyOnForm(),

            Boolean::make('checkin_over_slots', 'Can checkin over slots')
                ->onlyOnForm()
                ->sortable(),
            Boolean::make('display_slots_block', 'Display daily slots block')
                ->onlyOnForm()
                ->sortable(),
            Number::make('auto_checkout_duration', 'Auto checkout duration (min)')
                ->displayHandler(fn ($record) => $record->auto_checkout_after.' min')
                ->onlyOnForm()
                ->sortable(),

            Number::make('classes_slots')
                ->unfillableRecord()
                ->onlyOnForm()
                ->default(0),

            BelongsToMany::make('clubs', Club::class, 'relatedClubs')
                ->dependsOn('is_pooled_access', true)
                ->unfillableRecord(),
            HasMany::make('clubs', Club::class, 'clubsSlots')
                ->dependsOn('is_pooled_access', false)
                ->disableAddMore()
                ->disableDelete()
                ->onlyOnForm()
                ->fields([
                    Number::make('adult_slots')
                        ->rules('required'),
                    Number::make('kid_slots')
                        ->rules('required'),
                ])
                ->repeaterTitle(fn ($record) => $record->title),

        ];
    }

    public function layout(): array
    {
        return [
            VerticalTab::make('')
                ->attach([
                    TabElement::make('Basic Information')->attach([
                        'name',
                        'status',
                        'single_membership_forecast_price',
                        'family_membership_forecast_price',
                        'website',
                        'first_interaction',
                        'notes',
                    ]),
                    TabElement::make('Clubs')->attach([
                        'is_pooled_access',
                        'clubsSlots',
                        'relatedClubs',
                        'checkin_over_slots',
                        'display_slots_block',
                        'auto_checkout_duration',
                    ]),
                    TabElement::make('Price and Slots summary')->attach([
                        'current_contract_expiry',
                        'tranche_expiry',
                        'single_membership_price',
                        'family_membership_price',
                        'individual_kid_membership_price',
                        'purchased_single_membership',
                        'purchased_family_membership',
                        'purchased_kid_membership',
                        'adult_cost_per_visit',
                        'kid_cost_per_visit',
                        'contract_value',
                        'adult_slots',
                        'kid_slots',
                        'classes_slots',
                    ]),
                ]),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'name', 'partners.name')
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()->options(Partner::getConstOptions('statuses')),
                'status',
                'partners.status'
            )
                ->quick(),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'contract_expiry',
                'partners.contract_expiry'
            )
                ->quick(),
        ];
    }
}
