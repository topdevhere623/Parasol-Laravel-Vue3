<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Club\Club;
use App\Models\Member\MembershipDuration;
use App\Models\Member\MembershipType;
use App\Models\Package;
use App\Models\Payments\PaymentMethod;
use App\Models\Payments\PaymentType;
use App\Models\Plan;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Containers\TabElement;
use ParasolCRMV2\Containers\VerticalTab;
use ParasolCRMV2\Fields\BelongsTo;
use ParasolCRMV2\Fields\BelongsToMany;
use ParasolCRMV2\Fields\Boolean;
use ParasolCRMV2\Fields\Date;
use ParasolCRMV2\Fields\Field;
use ParasolCRMV2\Fields\Hidden;
use ParasolCRMV2\Fields\HorizontalRadioButton;
use ParasolCRMV2\Fields\Money;
use ParasolCRMV2\Fields\Number;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\BetweenFilter;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\SelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

class PlanResource extends ResourceScheme
{
    public static $model = Plan::class;

    public const STATUS_BADGES = [
        'inactive' => 'red',
        'active' => 'green',
    ];

    public const DEFAULT_PLAN_CLUB_OPTIONS = [
        'exclude' => 'Exclude',
        'include' => 'Include',
    ];

    public function tableQuery(Builder $query)
    {
        $query->addSelect('package.program_id')
            ->with('package')
            ->leftJoin(
                'plan_club as plan_club_fixed',
                fn ($join) => $join->on('plans.id', '=', 'plan_club_fixed.plan_id')->where(
                    'plan_club_fixed.type',
                    'fixed'
                )
            )->leftJoin(
                'plan_club as plan_club_include',
                fn ($join) => $join->on('plans.id', '=', 'plan_club_include.plan_id')->where(
                    'plan_club_include.type',
                    'include'
                )
            )->leftJoin(
                'plan_club as plan_club_exclude',
                fn ($join) => $join->on('plans.id', '=', 'plan_club_exclude.plan_id')->where(
                    'plan_club_exclude.type',
                    'exclude'
                )
            );
    }

    public function fields(): array
    {
        return [
            HorizontalRadioButton::make('status')
                ->options(Plan::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->default(Plan::STATUSES['active'])
                ->rules('required')
                ->sortable(),
            BelongsTo::make('program', Program::class)
                ->titleField('name')
                ->onlyOnTable()
                ->url('/programs/{program_id}')
                ->sortable(),
            BelongsTo::make('package', Package::class)
                ->rules('required')
                ->url('/packages/{package_id}')
                ->sortable(),
            Boolean::make('disable_renewal')
                ->setFromRecordHandler(fn ($record) => !$record->renewal_package_id)
                ->onlyOnForm()
                ->unfillableRecord()
                ->computed(),
            BelongsTo::make('renewalPackage', Package::class, 'renewalPackage', 'Renewal Package')
                ->dependsOn('disable_renewal', true, ['hide', 'set' => null])
                ->nullable()
                ->hideOnTable()
                ->url('/packages/{renewal_package_id}')
                ->sortable(),
            Select::make('renewal_email_type')
                ->dependsOn('disable_renewal', true, ['hide'])
                ->default(Plan::RENEWAL_EMAIL_TYPES['default'])
                ->options(Plan::getConstOptions('renewal_email_types'))
                ->onlyOnForm()
                ->sortable(),
            Text::make('title')
                ->rules('required')
                ->sortable(),
            Money::make('price')
                ->rules('required')
                ->sortable(),
            HorizontalRadioButton::make('vat_type', 'VAT')
                ->options(Plan::getConstOptions('vat_types'))
                ->onlyOnForm()
                ->default(Plan::VAT_TYPES['include'])
                ->rules('required'),

            Money::make('vat', 'VAT')
                ->computed()
                ->column('price')
                ->displayHandler(function (Plan $record) {
                    return $record->vat_amount.' '.substr($record->vat_type, 0, 4);
                })
                ->onlyOnTable()
                ->sortable(),
            Number::make('price_third_party_commission_percent')
                ->hideOnTable()
                ->sortable()
                ->default(0)
                ->tooltip('Percentage value'),
            Select::make('duration_type')
                ->sortable()
                ->options(Plan::getConstOptions('duration_types')),

            Text::make('duration')
                ->displayHandler(function (Plan $record) {
                    if ($record->duration_type == Plan::DURATION_TYPES['fixed_date']) {
                        return Carbon::parse($record->duration)->format(config('app.DATE_FORMAT'));
                    }
                    return $record->duration;
                })
                ->onlyOnTable()
                ->sortable(),
            Number::make('duration_number', 'Duration')
                ->onlyOnForm()
                ->dependsOn('duration_type', Plan::DURATION_TYPES['fixed_date'], ['hide'])
                ->setFromRecordHandler(function (Plan $record) {
                    return $record->duration_type != Plan::DURATION_TYPES['fixed_date'] ? $record->duration : null;
                })
                ->fillRecordHandler(function (Plan $record, Field $field) {
                    if (request('duration_type') != Plan::DURATION_TYPES['fixed_date']) {
                        $record->setAttribute('duration', $field->value);
                    }
                })
                ->rules('required_unless:duration_type,'.Plan::DURATION_TYPES['fixed_date']),

            Date::make('duration_date', 'Duration')
                ->dependsOn('duration_type', Plan::DURATION_TYPES['fixed_date'])
                ->onlyOnForm()
                ->setFromRecordHandler(function (Plan $record) {
                    return $record->duration_type == Plan::DURATION_TYPES['fixed_date'] ? $record->duration : null;
                })
                ->fillRecordHandler(function (Plan $record, Field $field) {
                    if (request('duration_type') == Plan::DURATION_TYPES['fixed_date']) {
                        $record->setAttribute('duration', $field->value);
                    }
                })
                ->rules('required_if:duration_type,'.Plan::DURATION_TYPES['fixed_date']),

            Number::make('check_ins_limit', 'Check-ins limit')
                ->tooltip('Check-ins limit before membership expiry')
                ->rules('required')
                ->sortable(),
            HorizontalRadioButton::make('allowed_club_type')
                ->options(Plan::getConstOptions('allowed_club_types'))
                ->default(Plan::ALLOWED_CLUB_TYPES['limited'])
                ->sortable()
                ->hideOnTable(),
            Number::make('number_of_clubs', 'The number of allowed clubs')
                ->dependsOn('allowed_club_type', Plan::ALLOWED_CLUB_TYPES['all_available'], ['hide', 'set' => 0])
                ->rules('required')
                ->sortable(),
            Boolean::make('is_partner_available')
                ->onlyOnForm(),
            Boolean::make('show_children_block', 'Children block visible')
                ->default(true)
                ->onlyOnForm(),
            Number::make('number_of_allowed_children')
                ->rules('required_if:show_children_block,true')
                ->default(4)
                ->dependsOn('show_children_block', true)
                ->onlyOnForm(),
            Number::make('number_of_free_children')
                ->rules('required_if:show_children_block,true')
                ->dependsOn('show_children_block', true)
                ->default(0)
                ->onlyOnForm(),
            Money::make('extra_child_price')
                ->rules('required_if:show_children_block,true')
                ->dependsOn('show_children_block', true)
                ->onlyOnForm(),
            Number::make('extra_child_third_party_commission_percent')
                ->default(0)
                ->hideOnTable()
                ->sortable()
                ->dependsOn('show_children_block', true)
                ->tooltip('Percentage value'),

            Number::make('number_of_allowed_juniors')
                ->rules('required_if:show_children_block,true')
                ->dependsOn('show_children_block', true)
                ->default(4)
                ->onlyOnForm(),
            Number::make('number_of_free_juniors', 'The number of free juniors')
                ->rules('required_if:show_children_block,true')
                ->dependsOn('show_children_block', true)
                ->default(0)
                ->onlyOnForm(),
            Money::make('extra_junior_price', 'Extra junior price')
                ->rules('required_if:show_children_block,true')
                ->dependsOn('show_children_block', true)
                ->onlyOnForm(),
            Number::make('extra_junior_third_party_commission_percent')
                ->default(0)
                ->hideOnTable()
                ->sortable()
                ->dependsOn('show_children_block', true)
                ->tooltip('Percentage value'),
            // Number::make('sort')
            //    ->hideOnTable()
            //    ->rules('required')
            //    ->sortable(),

            Boolean::make('is_coupon_conditional_purchase', 'Applicable only with coupon')
                ->onlyOnForm(),
            Boolean::make('show_start_date_on_booking')
                ->onlyOnForm(),

            Boolean::make('is_giftable', 'Plan can be giftable')
                ->onlyOnForm(),
            Boolean::make('is_family_plan_available', 'Show family plan on member portal')
                ->onlyOnForm(),

            BelongsToMany::make(
                'fixedVisibleInPlanClubs',
                Club::class,
                'fixedVisibleInPlanClubs',
                'Fixed selected clubs'
            )
                ->updateRelatedHandler(function ($record, $relation, BelongsToMany $field) {
                    $relation->syncWithPivotValues($field->getIds(), ['type' => 'fixed']);
                })
                ->multiple()
                ->optionHandler(fn ($query) => $query->visibleInPlan())
                ->dependsOn('allowed_club_type', Plan::ALLOWED_CLUB_TYPES['all_available'], ['hide'])
                ->onlyOnForm(),

            BelongsToMany::make(
                'excludedVisibleInPlanClubs',
                Club::class,
                'excludedVisibleInPlanClubs',
                'Excluded clubs'
            )
                ->updateRelatedHandler(
                    function (
                        $record,
                        \Illuminate\Database\Eloquent\Relations\BelongsToMany $relation,
                        BelongsToMany $field
                    ) {
                        if (request('exclude_or_include') == 'exclude') {
                            $record->includedVisibleInPlanClubs()->detach();
                            $relation->syncWithPivotValues($field->getIds(), ['type' => 'exclude']);
                        }
                    }
                )
                ->dependsOn('exclude_or_include', Plan::PLAN_CLUB_TYPES['exclude'], ['show'])
                ->dependentBehavior()
                ->multiple()
                ->optionHandler(fn ($query) => $query->visibleInPlan())
                ->onlyOnForm(),

            BelongsToMany::make(
                'includedVisibleInPlanClubs',
                Club::class,
                'includedVisibleInPlanClubs',
                'Included clubs'
            )
                ->updateRelatedHandler(function ($record, $relation, BelongsToMany $field) {
                    if (request('exclude_or_include') == 'include') {
                        $record->excludedVisibleInPlanClubs()->detach();
                        $relation->syncWithPivotValues($field->getIds(), ['type' => 'include']);
                    }
                })
                ->dependsOn('exclude_or_include', Plan::PLAN_CLUB_TYPES['include'], ['show'])
                ->dependentBehavior()
                ->multiple()
                ->optionHandler(fn ($query) => $query->visibleInPlan())
                ->onlyOnForm(),

            HorizontalRadioButton::make('exclude_or_include', 'Exclude or include')
                ->options(self::DEFAULT_PLAN_CLUB_OPTIONS)
                ->default(Plan::PLAN_CLUB_TYPES['exclude'])
                ->setFromRecordHandler(function ($record) {
                    return $record->excludedVisibleInPlanClubs()->count()
                        ? Plan::PLAN_CLUB_TYPES['exclude']
                        : Plan::PLAN_CLUB_TYPES['include'];
                })
                ->computed()
                ->unfillableRecord()
                ->onlyOnForm(),
            BelongsToMany::make('paymentMethods', PaymentMethod::class, 'payment_methods')
                ->multiple()
                ->onlyOnForm(),
            BelongsTo::make('membershipType', MembershipType::class, null, 'Membership Type'),
            Text::make('small_description')
                ->onlyOnForm(),
            Text::make('question_mark_description')
                ->onlyOnForm(),

            Text::make('Link')
                ->computed()
                ->displayHandler(fn ($record) => 'link')
                ->url(fn ($record) => route('booking.step-1', [
                    'package' => $record->package->slug ?? '',
                    'plan' => $record->id,
                ]))
                ->badges(['default' => 'blue'])
                ->onlyOnTable(),

            Hidden::make('link')
                ->setFromRecordHandler(fn ($record) => route('booking.step-1', [
                    'package' => $record->package->slug ?? '',
                    'plan' => $record->id,
                ]))
                ->unfillableRecord(),

            Text::make('Included clubs')
                ->computed()
                ->displayHandler(function ($record) {
                    $clubs = $record->includedVisibleInPlanClubs->pluck('title')->toArray();

                    return implode(', ', $clubs);
                })
                ->sortable('plan_club_include.club_id')
                ->onlyOnTable()
                ->hideOnTable(),

            Text::make('Excluded clubs')
                ->computed()
                ->sortable()
                ->displayHandler(function ($record) {
                    $clubs = $record->excludedVisibleInPlanClubs->pluck('title')->toArray();

                    return implode(', ', $clubs);
                })
                ->sortable('plan_club_exclude.club_id')
                ->onlyOnTable()
                ->hideOnTable(),

            Text::make('Fixed clubs')
                ->computed()
                ->displayHandler(function ($record) {
                    $clubs = $record->fixedVisibleInPlanClubs->pluck('title')->toArray();

                    return implode(', ', $clubs);
                })
                ->sortable('plan_club_fixed.club_id')
                ->onlyOnTable()
                ->hideOnTable(),
            BelongsTo::make('paymentType', PaymentType::class, null, 'Payment Type'),
            BelongsTo::make('membershipDuration', MembershipDuration::class, null, 'Membership Duration')
                ->hideOnTable()
                ->tooltip('Will be assigned to member on booking'),
        ];
    }

    public function layout(): array
    {
        return [
            VerticalTab::make('')->attach([
                TabElement::make('Basic details')->attach([
                    'status',
                    'package',
                    'disable_renewal',
                    'renewalPackage',
                    'renewal_email_type',
                    'title',
                    'price',
                    'vat_type',
                    'vat',
                    'price_third_party_commission_percent',
                    'duration_type',
                    'duration_number',
                    'duration_date',
                    'check_ins_limit',
                    'is_partner_available',
                    'show_children_block',
                    'number_of_allowed_children',
                    'number_of_free_children',
                    'extra_child_price',
                    'extra_child_third_party_commission_percent',
                    'number_of_allowed_juniors',
                    'number_of_free_juniors',
                    'extra_junior_price',
                    'extra_junior_third_party_commission_percent',
                    'sort',
                    'is_coupon_conditional_purchase',
                    'is_giftable',
                    'is_family_plan_available',
                    'show_start_date_on_booking',
                    'payment_methods',
                    'paymentType',
                    'membershipDuration',
                ]),
                TabElement::make('Clubs')->attach([
                    'allowed_club_type',
                    'number_of_clubs',
                    'fixedVisibleInPlanClubs',
                    'exclude_or_include',
                    'excludedVisibleInPlanClubs',
                    'includedVisibleInPlanClubs',
                ]),
                TabElement::make('Description')->attach([
                    'membershipType',
                    'small_description',
                    'question_mark_description',
                ]),
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'title', 'plans.title')
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Plan::getConstOptions('statuses')),
                'status',
                'plans.status'
            )
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options($this->getPackages()),
                'package',
                'plans.package_id'
            )
                ->quick(),
            BetweenFilter::make(
                new TextFilterField(),
                new TextFilterField(),
                'price',
                'price',
            ),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Plan::VAT_TYPES),
                'vat_type',
                'vat_type'
            ),
            InFilter::make(
                SelectFilterField::make()
                    ->options(['day' => 'Day', 'month' => 'Month', 'year' => 'Year']),
                'duration_type'
            ),
            BetweenFilter::make(
                new TextFilterField(),
                new TextFilterField(),
                'duration',
                'duration',
            ),
            BetweenFilter::make(
                new TextFilterField(),
                new TextFilterField(),
                'extra_child_price',
                'extra_child_price',
            ),
            BetweenFilter::make(
                new TextFilterField(),
                new TextFilterField(),
                'extra_junior_price',
                'extra_junior_price',
            ),
            EqualFilter::make(
                SelectFilterField::make()
                    ->options([
                        0 => 'No',
                        1 => 'Yes',
                    ]),
                'is_partner_available',
                'plans.is_partner_available',
            ),
            InFilter::make(
                SelectFilterField::make()
                    ->options($this->getNumbers(0, 10)),
                'number_of_free_children'
            ),
            InFilter::make(
                SelectFilterField::make()
                    ->options($this->getNumbers(0, 10)),
                'number_of_free_juniors'
            ),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Program::getSelectable()),
                'program',
                'package.program_id'
            ),
            InFilter::make(
                SelectFilterField::make()
                    ->options($this->getClubs()),
                'included_club',
                'plan_club_include.club_id'
            ),
            InFilter::make(
                SelectFilterField::make()
                    ->options($this->getClubs()),
                'excluded_club',
                'plan_club_exclude.club_id'
            ),
            InFilter::make(
                SelectFilterField::make()
                    ->options($this->getClubs()),
                'fixed_club',
                'plan_club_fixed.club_id'
            ),
        ];
    }

    public function layoutFilters(): array
    {
        return [
            Group::make('')->attach([
                'title',
                'status',
                'package_id',
                'price',
                'duration_type',
                'duration',
                'extra_child_price',
                'extra_junior_price',
                'number_of_adults',
                'number_of_free_children',
                'number_of_free_juniors',
                'included_club',
                'excluded_club',
                'fixed_club',
            ]),
        ];
    }

    protected function getPackages(): array
    {
        return Package::orderBy('title')
            ->pluck('title', 'id')
            ->toArray();
    }

    protected function getClubs(): array
    {
        return Club::orderBy('title')
            ->pluck('title', 'id')
            ->toArray();
    }

    protected function planClubRelationHandler($type): \Closure
    {
        return function ($record, $relation, BelongsToMany $field) use ($type) {
            $relation->syncWithPivotValues([], ['type' => 'include']);
            $relation->syncWithPivotValues($field->getIds(), ['type' => $type]);
        };
    }

    private function getNumbers(int $start, int $end)
    {
        $nums = [];

        for ($i = $start; $i <= $end; $i++) {
            $nums[$i] = $i;
        }

        return $nums;
    }
}
