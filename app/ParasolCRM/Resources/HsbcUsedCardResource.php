<?php

namespace App\ParasolCRM\Resources;

use App\Models\Booking;
use App\Models\HSBCUsedCard;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Money;
use ParasolCRM\Fields\Number;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\BetweenFilter;
use ParasolCRM\Filters\Fields\DateFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class HsbcUsedCardResource extends ResourceScheme
{
    public static $model = HSBCUsedCard::class;

    public const STATUS_BADGES = [
        'completed' => 'green',
        'cancelled' => 'orange',
        'refunded' => 'gray',
    ];

    public function tableQuery(Builder $query)
    {
        $query->with('booking', 'plan')
            ->select(
                collect(
                    [
                        'subtotal_amount',
                        'discount_amount',
                        'total_amount_without_vat',
                        'vat_amount',
                        'total_amount',
                        'third_party_commission_amount',
                    ]
                )->map(fn ($item) => 'payments.'.$item)
                    ->toArray()
            )
            ->leftJoinRelationship('payment.booking');
    }

    public function fields(): array
    {
        return [
            Text::make('booking_id', 'Booking ID')
                ->displayHandler(fn ($record) => optional($record->booking)->reference_id)
                ->url(
                    fn ($record) => $record->booking && \Prsl::checkGatePolicy(
                        'view',
                        Booking::class,
                        $record->booking
                    )
                        ? ('/bookings/'.$record->booking_id.'/view')
                        : null
                )
                ->sortable()
                ->onlyOnTable(),
            HorizontalRadioButton::make('status')
                ->options(HSBCUsedCard::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->sortable(),
            Text::make('plan_id', 'Plan')
                ->url(
                    fn ($record) => \Prsl::checkGatePolicy('update', Plan::class, $record->plan)
                        ? ('/plans/'.$record->plan_id)
                        : null
                )
                ->displayHandler(fn ($record) => optional($record->plan)->title)
                ->hideOnTable()
                ->sortable()
                ->onlyOnTable(),
            Number::make('bin')
                ->sortable()
                ->onlyOnTable(),
            Text::make('card_last4_digits')
                ->onlyOnTable(),
            Text::make('card_scheme')
                ->hideOnTable()
                ->onlyOnTable(),
            Date::make('card_expiry_date')
                ->hideOnTable()
                ->onlyOnTable(),
            Money::make('subtotal_amount', 'Subtotal amount (B/F discount)')
                ->rules('required')
                ->onlyOnTable(),
            Money::make('discount_amount')
                ->rules('required')
                ->onlyOnTable(),
            Money::make('total_amount_without_vat')
                ->onlyOnTable(),
            Money::make('vat_amount')
                ->onlyOnTable(),
            Money::make('total_amount')
                ->onlyOnTable(),
            Money::make('third_party_commission_amount', 'Payable to TE')
                ->sortable('payments.third_party_commission_amount')
                ->onlyOnTable(),
            Date::make('refunded_at', 'Refund date')
                ->sortable()
                ->hideOnTable()
                ->nullable(),
            Text::make('refund_amount')
                ->sortable()
                ->hideOnTable()
                ->nullable(),
            Date::make('canceled_at', 'Cancellation Date')
                ->sortable()
                ->hideOnTable()
                ->nullable(),
            Date::make('created_at', 'Created date')
                ->sortable()
                ->onlyOnTable(),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(new TextFilterField(), 'card_last4_digits'),
            LikeFilter::make(new TextFilterField(), 'card_scheme'),
            LikeFilter::make(new TextFilterField(), 'bin')->quick(),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'created_at',
                'hsbc_used_cards.created_at',
                'Created Date'
            )
                ->quick(),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'card_expiry_date',
                'card_expiry_date',
                'Card Expiry Date'
            ),
        ];
    }

    public function layoutFilters(): array
    {
        return [
            Group::make('')->attach([
                'bin',
                'card_last4_digits',
                'card_scheme',
                'created_at',
                'card_expiry_date',
            ]),
        ];
    }

    public static function label(): string
    {
        return 'HSBC payments';
    }

    public static function singularLabel(): string
    {
        return 'HSBC payments';
    }
}
