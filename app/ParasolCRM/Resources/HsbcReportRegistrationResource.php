<?php

namespace App\ParasolCRM\Resources;

use App\Models\Booking;
use App\Models\HSBCUsedCard;
use App\Models\Member\Member;
use App\Models\Payments\Payment;
use App\Models\Plan;
use App\Models\Reports\HSBCReport;
use App\ParasolCRM\Filters\HsbcRegistrationTypeFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\DateTime;
use ParasolCRM\Fields\Email;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Money;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\BetweenFilter;
use ParasolCRM\Filters\Fields\DateFilterField;
use ParasolCRM\Filters\Fields\SelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class HsbcReportRegistrationResource extends ResourceScheme
{
    public static string $model = HSBCReport::class;

    public const PACKAGE_TYPE_BADGES = [
        Plan::HSBC_SINGLE_FREE => 'green',
        'default' => 'orange',
    ];

    public const STATUS_BADGES = [
        'completed' => 'green',
        'cancelled' => 'red',
        'refunded' => 'orange',
        'unknown' => 'default',
    ];

    public function tableQuery(Builder $query)
    {
        $query->with('booking.payment', 'plan')
            ->where('bookings.type', Booking::TYPES['booking'])
            ->where('members.membership_status', Member::MEMBERSHIP_STATUSES['active'])
            ->select('payments.payment_date')
            ->addSelect(
                collect(
                    ['first_name', 'last_name', 'phone', 'member_id as membership_number', 'login_email', 'member_type']
                )->map(fn ($item) => 'members.'.$item)
                    ->toArray()
            )
            ->join('bookings', 'bookings.id', '=', 'hsbc_used_cards.booking_id')
            ->join('payments', function ($join) {
                $join->on('bookings.payment_id', '=', 'payments.id')
                    ->whereIn('payments.status', [Payment::STATUSES['paid'], Payment::STATUSES['partial_refunded']]);
            })
            ->join('members', function (JoinClause $query) {
                $query->whereRaw('members.id = hsbc_used_cards.member_id');
            })
            ->groupBy('hsbc_used_cards.id');
    }

    public function fields(): array
    {
        return [
            Text::make('membership_number', 'Soleil Member #')
                ->url(
                    fn ($record) => \Prsl::checkGatePolicy(
                        'update',
                        Member::class,
                        $record
                    ) ? ('/member-primary/'.$record->member_id) : null
                )
                ->sortable()
                ->onlyOnTable(),
            HorizontalRadioButton::make('member_type')
                ->options(Member::getConstOptions('MEMBER_TYPES'))
                ->badges(MemberResource::MEMBER_TYPE_BADGES)
                ->hideOnTable(),
            Text::make('first_name')
                ->sortable()
                ->onlyOnTable(),
            Text::make('last_name')
                ->sortable()
                ->onlyOnTable(),
            Text::make('phone')
                ->sortable()
                ->onlyOnTable(),
            Email::make('login_email', 'Email')
                ->sortable()
                ->onlyOnTable(),
            BelongsTo::make('plan', Plan::class, 'plan', 'Product type')
                ->url(
                    fn ($record) => \Prsl::checkGatePolicy('update', Plan::class, $record->plan)
                        ? ('/plans/'.$record->plan_id)
                        : null
                )
                ->sortable()
                ->onlyOnTable(),
            Text::make('plan_id', 'Complementary/Paid')
                ->displayHandler(fn ($model) => $model->plan_id === Plan::HSBC_SINGLE_FREE ? 'Complementary' : 'Paid')
                ->badges(self::PACKAGE_TYPE_BADGES)
                ->sortable()
                ->onlyOnTable(),
            Text::make('amount')
                ->computed()
                ->displayHandler(
                    fn ($model) => money_formatter(
                        $model->member_type == Member::MEMBER_TYPES['member']
                            ? $model->booking?->total_price : 0
                    )
                )
                ->onlyOnTable(),

            DateTime::make('payment_date', 'Date')
                ->onlyOnTable(),
            HorizontalRadioButton::make('status')
                ->options(HSBCUsedCard::getConstOptions('statuses'))
                ->badges(HsbcUsedCardResource::STATUS_BADGES)
                ->sortable(),

            Date::make('refunded_at', 'Refund date')
                ->sortable()
                ->hideOnTable()
                ->nullable(),
            Money::make('refund_amount')
                ->sortable()
                ->hideOnTable()
                ->nullable(),
            Date::make('canceled_at', 'Cancellation Date')
                ->sortable()
                ->hideOnTable()
                ->nullable(),
            Text::make('card_last4_digits')
                ->computed()
                ->displayHandler(fn ($model) => optional(optional($model->booking)->hsbcUsedCard)->card_last4_digits)
                ->onlyOnTable(),
        ];
    }

    public function filters(): array
    {
        return [
            HsbcRegistrationTypeFilter::make(
                SelectFilterField::make()->options([
                    'complimentary' => 'Complimentary + Complimentary Family',
                    'paid' => 'Paid',
                ]),
                'type',
                'members.plan_id',
                'Product type'
            )->quick(),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'members.start_date',
                null,
                'Start Date'
            )
                ->quick(),
            BetweenFilter::make(DateFilterField::make(), DateFilterField::make(), 'members.end_date', null, 'End Date')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'members.first_name', null, 'First name')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'members.last_name', null, 'Last name')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'members.phone', 'members.phone', 'Phone'),
            LikeFilter::make(TextFilterField::make(), 'login_email', 'login_email', 'Email')
                ->quick(),
        ];
    }

    public static function label()
    {
        $isAdmin = auth()->user()->isAdmin();
        return $isAdmin ? 'Reports | HSBC Registrations' : 'Registrations';
    }
}
