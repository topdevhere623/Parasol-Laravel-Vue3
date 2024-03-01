<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Package;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentMethod;
use App\Models\Payments\PaymentTransaction;
use App\Models\Payments\PaymentType;
use App\Models\Plan;
use App\Models\Program;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Fields\BelongsTo;
use ParasolCRMV2\Fields\Date;
use ParasolCRMV2\Fields\DateTime;
use ParasolCRMV2\Fields\Money;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Textarea;
use ParasolCRMV2\Filters\BetweenFilter;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;
use ParasolCRMV2\Statuses\DoughnutStatus;
use ParasolCRMV2\Statuses\TextStatus;

class PaymentTransactionResource extends ResourceScheme
{
    public static $model = PaymentTransaction::class;

    public const STATUS_BADGES = [
        'success' => 'green',
        'fail' => 'red',
        'pending' => 'orange',
        'cancel' => 'light',
    ];

    public const TYPE_BADGES = [
        'authorize' => 'blue',
        'capture' => 'green',
        'refund' => 'gray',
    ];

    //    public function tableQuery(Builder $query)
    //    {
    //        $query
    //            ->leftJoin('payments', 'payment_transactions.payment_id', '=', 'payments.id');
    //    }
    //
    //    public function statusQuery(Builder $query)
    //    {
    //        $query
    //            ->leftJoin('booking_payment', 'payments.id', '=', 'booking_payment.payment_id')
    //            ->leftJoin('bookings', 'booking_payment.booking_id', '=', 'bookings.id')
    //            ->leftJoin('members', 'payments.member_id', '=', 'members.id')
    //            ->leftJoin('plans', 'members.plan_id', '=', 'plans.id')
    //            ->leftJoin('packages', 'members.package_id', '=', 'packages.id')
    //            ->leftJoin('programs', 'members.program_id', '=', 'programs.id')
    //            ->select(
    //                'members.id as program_id',
    //                'programs.id as program_id',
    //                'programs.name as program_name',
    //                'packages.id as package_id',
    //                'packages.title as package_title',
    //                'plans.id as plan_id',
    //                'plans.title as plan_title',
    //                'booking_payment.*'
    //            );
    //    }

    public function fields(): array
    {
        return [
            Select::make('status')
                ->options(PaymentTransaction::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            Select::make('type')
                ->options(PaymentTransaction::getConstOptions('types'))
                ->badges(self::TYPE_BADGES)
                ->rules('required')
                ->sortable(),
            BelongsTo::make('payment', Payment::class, null, 'Payment')
                ->url('/payments/{payment_id}')
                ->rules('required')
                ->titleField('reference_id')
                ->optionHandler(function ($query) {
                    return $query->reorder()->latest();
                })
                ->sortable(),
            Money::make('amount'),
            BelongsTo::make('paymentMethod', PaymentMethod::class, null, 'Payment Method')
                ->url('/payment-methods/{payment_method_id}')
                ->rules('required')
                ->sortable(),
            Textarea::make('description')
                ->onlyOnForm()
                ->dependsOn('description', null, ['hide'])
                ->unfillableRecord(),

            DateTime::make('created_at')
                ->onlyOnTable()
                ->sortable(),

        ];
    }

//    public function layout(): array
//    {
//        return [
//            Group::make('')->attach([
//                'status',
//                'reference_id',
//                'member_id',
//                'subtotal_amount',
//                'discount_amount',
//                'member',
//                'paymentMethod',
//                'paymentType',
//                'offer_code',
//                'payment_date',
//            ]),
//            Group::make('files')->attach([
//                'files',
//            ]),
//        ];
//    }

    public function filters(): array
    {
        return [
            EqualFilter::make(TextFilterField::make(), 'payment_id', 'payment.id')
                ->hidden(),

            InFilter::make(
                (new MultipleSelectFilterField())->options(PaymentTransaction::STATUSES),
                'status',
                'payment_transactions.status'
            )->quick(),

            InFilter::make(
                (new MultipleSelectFilterField())->options(PaymentTransaction::TYPES),
                'type',
                'payment_transactions.type'
            )->quick(),

            BetweenFilter::make(
                new TextFilterField(),
                new TextFilterField(),
                'amount',
                'payment_transactions.amount',
            )->quick(),

            LikeFilter::make(new TextFilterField(), 'reference_id', 'payment.reference_id', 'Payment')
                ->quick(),

            BetweenFilter::make(
                new DateFilterField(),
                new DateFilterField(),
                'created_at',
                'payment_transactions.created_at',
            )->quick(),

            InFilter::make(
                (new MultipleSelectFilterField())->endpoint('payment/relation-options/paymentMethod'),
                'paymentMethod',
                'paymentMethod.id',
                'Payment Method'
            ),
        ];
    }

    public function layoutFilters(): array
    {
        return [
            Group::make('')->attach([
                'status',
                'type',
                'amount',
                'reference_id',
                'paymentMethod',
                'created_at',
            ]),
        ];
    }

    public function statuses(): array
    {
        return [
            DoughnutStatus::make('Statuses')
                ->count('payment_transactions.status')
                ->labels(PaymentTransaction::getConstOptions('statuses'))
                ->colors(self::STATUS_BADGES),
            DoughnutStatus::make('Types')
                ->count('payment_transactions.type')
                ->labels(PaymentTransaction::getConstOptions('types'))
                ->colors(self::TYPE_BADGES),
            TextStatus::make('Amount', fn ($query) => money_formatter($query->sum('amount'))),
        ];
    }

    public static function singularLabel(): string
    {
        return 'Transactions';
    }

    public static function label(): string
    {
        return 'Transactions';
    }
}
