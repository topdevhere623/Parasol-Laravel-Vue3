<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Booking;
use App\Models\Member\Member;
use App\Models\Member\MemberPaymentSchedule;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Fields\Avatar;
use ParasolCRMV2\Fields\BelongsTo;
use ParasolCRMV2\Fields\Date;
use ParasolCRMV2\Fields\DateTime;
use ParasolCRMV2\Fields\Money;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\BetweenFilter;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;
use ParasolCRMV2\Statuses\DoughnutStatus;
use ParasolCRMV2\Statuses\TextStatus;

class MemberPaymentScheduleResource extends ResourceScheme
{
    public static $model = MemberPaymentSchedule::class;

    public const STATUS_BADGES = [
        'active' => 'green',
        'inactive' => 'default',
        'stopped' => 'default',
        'failed' => 'red',
    ];
    public const CARD_STATUS_BADGES = [
        'active' => 'green',
        'expired' => 'red',
        'failed' => 'red',
    ];

    public function tableQuery(Builder $query)
    {
        return $query->with('booking', 'member');
    }

    public function fields(): array
    {
        return [
            Avatar::make('avatar')
                ->displayHandler(fn ($record) => file_url($record->member, 'avatar', 'small'))
                ->username('full_name')
                ->onlyOnTable(),
            Select::make('status')
                ->options(static::$model::getConstOptions('status'))
                ->badges(static::STATUS_BADGES)
                ->sortable(),
            BelongsTo::make('booking', Booking::class)
                ->titleField('reference_id')
                ->url(
                    fn ($record) => $record->booking && \PrslV2::checkGatePolicy('view', Booking::class, $record->booking)
                        ? ('/bookings/'.$record->booking->id.'/view')
                        : null
                )
                ->sortable()
                ->onlyOnTable(),
            BelongsTo::make('member', Member::class)
                ->titleField('member_id')
                ->url(
                    fn ($record) => $record->member && \PrslV2::checkGatePolicy('view', Member::class, $record->member)
                        ? ('/member-primary/'.$record->member->id)
                        : null
                )
                ->sortable()
                ->onlyOnTable(),
            Money::make('monthly_amount')
                ->onlyOnTable()
                ->sortable(),
            Money::make('third_party_commission_amount', '3rd party commission')
                ->hideOnTable()
                ->sortable(),
            Date::make('charge_date', 'Next charge date')
                ->displayHandler(
                    fn (
                        $record,
                        $field
                    ) => $record->status === MemberPaymentSchedule::STATUS['completed'] ? 'n\a' : $field->displayValue(
                        $record
                    )
                )
                ->rules(['date', 'required']),
            Money::make('first_payment_amount')
                ->sortable(),
            Select::make('card_status')
                ->options(static::$model::getConstOptions('status'))
                ->badges(static::STATUS_BADGES)
                ->sortable(),
            Text::make('card_last4_digits')
                ->onlyOnTable(),
            Text::make('card_scheme')
                ->hideOnTable()
                ->onlyOnTable(),
            Date::make('card_expiry_date')
                ->hideOnTable()
                ->onlyOnTable(),

            DateTime::make('created_at', 'Created date')
                ->displayHandler(function ($record) {
                    return Carbon::parse($record->created_at)->format(config('app.DATE_FORMAT'));
                })
                ->sortable()
                ->onlyOnTable(),
        ];
    }

    public function statuses(): array
    {
        return [
            TextStatus::make('Total monthly payments', fn ($query) => $query->count()),
            DoughnutStatus::make('Programs')
                ->count('member.program_id')
                ->labels(Program::pluck('name', 'id')->toArray()),
            TextStatus::make(
                'Next month cycle charge',
                fn ($query) => booking_amount_round(
                    $query->where('charge_date', now()->addMonth()->startOfMonth())->sum('monthly_amount')
                )
            ),
            TextStatus::make(
                'Total monthly revenue',
                fn ($query) => booking_amount_round($query->sum('monthly_amount'))
            ),
        ];
    }

    public function filters(): array
    {
        return [
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(static::$model::getConstOptions('status')),
                'status',
                'member_payment_schedules.status'
            )->quick(),
            LikeFilter::make(new TextFilterField(), 'booking', 'booking.reference_id')
                ->quick(),
            LikeFilter::make(new TextFilterField(), 'member', 'member.member_id')
                ->quick(),
            BetweenFilter::make(new DateFilterField(), new DateFilterField(), 'charge_date')
                ->quick(),
            BetweenFilter::make(
                new DateFilterField(),
                new DateFilterField(),
                'created_at',
                'member_payment_schedules.created_at',
                'Created date'
            )
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Program::getSelectable()),
                'program',
                'member.program_id'
            )->quick(),
        ];
    }

    public function layoutFilters(): array
    {
        return [
            Group::make('')->attach([
                'status',
                'booking',
                'member',
                'charge_date',
                'created_at',
                'program',
            ]),
        ];
    }

    public static function label(): string
    {
        return 'Member scheduled payments';
    }

    public static function singularLabel(): string
    {
        return 'Member scheduled payments';
    }
}
