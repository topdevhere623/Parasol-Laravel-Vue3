<?php

namespace App\ParasolCRM\Resources;

use App\Models\BackofficeUser;
use App\Models\Channel;
use App\Models\Coupon;
use App\Models\Member\Member;
use App\Models\Plan;
use App\ParasolCRM\Filters\CouponOwnerFilter;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\BelongsToMany;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Number;
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

class CouponResource extends ResourceScheme
{
    public static $model = Coupon::class;

    protected const STATUS_BADGES = [
        'inactive' => 'gray',
        'active' => 'green',
        'expired' => 'red',
        'redeemed' => 'blue',
    ];

    protected const AMOUNT_TYPE_BADGES = [
        'percentage' => 'green',
        'fixed' => 'blue',
    ];

    protected const TYPE_BADGES = [
        'bulk' => 'red',
        'individually' => 'blue',
        'referral' => 'green',
    ];

    public function tableQuery(Builder $query)
    {
        $query->with('couponable')
            ->leftJoin('members', function (QueryBuilder $query) {
                $query->where('couponable_id', '=', DB::raw('members.id'));
                $query->where('couponable_type', '=', Member::class);
            })
            ->leftJoin('backoffice_users', function (QueryBuilder $query) {
                $query->where('couponable_id', '=', DB::raw('backoffice_users.id'));
                $query->where('couponable_type', '=', BackofficeUser::class);
            });
    }

    public function fields(): array
    {
        return [
            HorizontalRadioButton::make('status')
                ->options(Coupon::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            Text::make('code')
                ->default(Str::random(Coupon::DEFAULT_CODE_LENGTH))
                ->rules(['required', 'unique:'.self::$model.',code'])
                ->sortable(),
            BelongsTo::make('channel', Channel::class)
                ->rules('required')
                ->sortable(),
            Select::make('type')
                ->options(Coupon::getConstOptions('types'))
                ->badges(self::TYPE_BADGES)
                ->rules('required')
                ->sortable(),
            Text::make('member', 'Owner')
                ->setFromRecordHandler(
                    fn ($record) => $record->type == Coupon::TYPES['referral'] ? $record->couponable?->member_id : null
                )
                ->unfillableRecord()
                ->dependsOn('type', Coupon::TYPES['referral'])
                ->onlyOnForm(),
            Select::make('backoffice_user', 'Owner')
                ->setFromRecordHandler(
                    fn (
                        $record
                    ) => $record->type != Coupon::TYPES['referral'] ? $record->couponable_id : null
                )
                ->fillRecordHandler(function (Coupon $record, Select $field) {
                    if ($record->type != Coupon::TYPES['referral']) {
                        $record->couponable()->associate(BackofficeUser::find($field->getValue()));
                    }
                })
                ->rules('required_if:type,'.Coupon::TYPES['individually'])
                ->default(auth()->id())
                ->options($this->getBackofficeUsers())
                ->dependsOn('type', Coupon::TYPES['referral'], ['hide'])
                ->onlyOnForm(),

            Text::make('couponable', 'Owner')
                ->displayHandler(
                    function (Coupon $record) {
                        $couponable = $record->couponable;

                        return $couponable instanceof Member ? $couponable?->member_id : $couponable?->full_name;
                    }
                )
                ->url(function ($record) {
                    $url = $record->couponable instanceof Member ? 'member-primary' : 'admins';

                    return "/{$url}/{$record->couponable_id}";
                })
                ->onlyOnTable(),
            Select::make('amount_type')
                ->options(Coupon::getConstOptions('amount_types'))
                ->rules('required')
                ->badges(self::AMOUNT_TYPE_BADGES)
                ->sortable(),
            Number::make('amount')
                ->default(Coupon::DEFAULT_AMOUNT)
                ->currency('%')
                ->rules('required')
                ->sortable(),
            Number::make('usage_limit')
                ->default(Coupon::DEFAULT_LIMIT)
                ->rules('required')
                ->sortable(),
            Number::make('number_of_used')
                ->sortable()
                ->onlyOnTable(),
            Date::make('expiry_date')
                ->rules('required')
                ->sortable(),
            Text::make('note')
                ->placeholder(Coupon::DEFAULT_NOTE)
                ->onlyOnForm(),
            Text::make('corporate_name')
                ->onlyOnForm(),
            Text::make('email_domain')
                ->tooltip('For multiple domains, list them separated by commas')
                ->onlyOnForm(),
            BelongsToMany::make('excludedPlans', Plan::class, 'excludedPlans', 'Excluded plans')
                ->updateRelatedHandler(
                    function (
                        $record,
                        \Illuminate\Database\Eloquent\Relations\BelongsToMany $relation,
                        BelongsToMany $field
                    ) {
                        if (request('exclude_or_include') == 'exclude') {
                            $record->includedPlans()->detach();
                            $relation->syncWithPivotValues($field->getIds(), ['type' => 'exclude']);
                        }
                    }
                )
                ->dependsOn('exclude_or_include', Coupon::PLAN_TYPES['exclude'], ['show'])
                ->dependentBehavior()
                ->multiple()
                ->optionHandler(fn ($query) => $query->active())
                ->onlyOnForm(),
            BelongsToMany::make('includedPlans', Plan::class, 'includedPlans', 'Included plans')
                ->updateRelatedHandler(
                    function (
                        $record,
                        \Illuminate\Database\Eloquent\Relations\BelongsToMany $relation,
                        BelongsToMany $field
                    ) {
                        if (request('exclude_or_include') == 'include') {
                            $record->excludedPlans()->detach();
                            $relation->syncWithPivotValues($field->getIds(), ['type' => 'include']);
                        }
                    }
                )
                ->dependsOn('exclude_or_include', Coupon::PLAN_TYPES['include'], ['show'])
                ->dependentBehavior()
                ->multiple()
                ->optionHandler(fn ($query) => $query->active())
                ->onlyOnForm(),
            HorizontalRadioButton::make('exclude_or_include', 'Exclude or include')
                ->options(Coupon::getConstOptions('plan_types'))
                ->default(Plan::const('Plan club types', 'Exclude'))
                ->setFromRecordHandler(function ($record) {
                    return $record->excludedPlans()->count()
                        ? Coupon::PLAN_TYPES['exclude']
                        : Coupon::PLAN_TYPES['include'];
                })
                ->computed()
                ->unfillableRecord()
                ->onlyOnForm(),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('Basic details')->attach([
                'channel',
                'status',
                'type',
                'code',
                'amount_type',
                'amount',
                'member',
                'backoffice_user',
                'note',
                'corporate_name',
                'usage_limit',
                'email_domain',
                'expiry_date',
            ]),
            Group::make('Plans')->attach([
                'exclude_or_include',
                'excludedPlans',
                'includedPlans',
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'code')
                ->quick(),
            CouponOwnerFilter::make(TextFilterField::make(), 'owner')
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Coupon::getConstOptions('statuses')),
                'status',
                'coupons.status'
            )
                ->quick(),
            EqualFilter::make(TextFilterField::make(), 'amount'),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Coupon::getConstOptions('amount_types')),
                'amount_type',
                'coupons.amount_type'
            ),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Coupon::getConstOptions('types')),
                'type',
                'coupons.amount_type',
            ),
            EqualFilter::make(TextFilterField::make(), 'usage_limit'),
            EqualFilter::make(TextFilterField::make(), 'number_of_used'),
            BetweenFilter::make(DateFilterField::make(), DateFilterField::make(), 'expiry_date'),
            LikeFilter::make(TextFilterField::make(), 'corporate_name', 'coupons.corporate_name'),
            EqualFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Channel::all()->pluck('title', 'id')->toArray()),
                'channel',
                'channel_id'
            ),
        ];
    }

    public function layoutFilters(): array
    {
        return [
            Group::make('')->attach([
                'code',
                'status',
                'type',
                'channel',
                'amount',
                'amount_type',
                'usage_limit',
                'number_of_used',
                'expiry_date',
                'corporate_name',
            ]),
        ];
    }

    public function getBackofficeUsers(): array
    {
        return BackofficeUser::oldest('first_name')
            ->whereHasTeam(BackofficeUser::TEAM)
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();
    }
}
