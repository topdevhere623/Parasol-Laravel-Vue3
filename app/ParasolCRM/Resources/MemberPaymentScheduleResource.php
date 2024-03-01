<?php

namespace App\ParasolCRM\Resources;

use App\Models\Booking;
use App\Models\Member\Member;
use App\Models\Member\MemberPaymentSchedule;
use App\Models\Payments\PaymentMethod;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\Avatar;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\DateTime;
use ParasolCRM\Fields\Money;
use ParasolCRM\Fields\Select;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\BetweenFilter;
use ParasolCRM\Filters\Fields\DateFilterField;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;
use ParasolCRM\Statuses\DoughnutStatus;
use ParasolCRM\Statuses\TextStatus;

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
                    fn ($record) => $record->booking && \Prsl::checkGatePolicy('view', Booking::class, $record->booking)
                        ? ('/bookings/'.$record->booking->id.'/view')
                        : null
                )
                ->sortable()
                ->onlyOnTable(),
            BelongsTo::make('member', Member::class)
                ->titleField('member_id')
                ->url(
                    fn ($record) => $record->member && \Prsl::checkGatePolicy('view', Member::class, $record->member)
                        ? ('/member-primary/'.$record->member->id)
                        : null
                )
                ->sortable()
                ->onlyOnTable(),
            BelongsTo::make('paymentMethod', PaymentMethod::class, null, 'Payment Method')
                ->url(
                    \Prsl::checkGatePolicy(
                        'view',
                        PaymentMethod::class
                    ) ? '/payment-methods/{payment_method_id}' : null
                )
                ->rules('required')
                ->hideOnTable()
                ->sortable(),
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
            InFilter::make(
                (new MultipleSelectFilterField())->endpoint('payment/relation-options/paymentMethod'),
                'paymentMethod',
                'paymentMethod.id'
            ),
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
                'paymentMethod',
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
