<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Booking;
use App\Models\Member\Member;
use App\Models\Member\MemberPrimary;
use App\Models\Member\MembershipType;
use App\Models\Package;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentFile;
use App\Models\Payments\PaymentMethod;
use App\Models\Payments\PaymentType;
use App\Models\Plan;
use App\Models\Program;
use App\Models\Zoho\ZohoInvoice;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Fields\BelongsTo;
use ParasolCRMV2\Fields\Date;
use ParasolCRMV2\Fields\DateTime;
use ParasolCRMV2\Fields\File;
use ParasolCRMV2\Fields\HasMany;
use ParasolCRMV2\Fields\Money;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
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

class PaymentResource extends ResourceScheme
{
    public static $model = Payment::class;

    public const STATUS_BADGES = [
        'paid' => 'green',
        'failed' => 'red',
        'pending' => 'orange',
        'refunded' => 'gray',
        'partial_refunded' => 'gray',
    ];

    public function query(Builder $query)
    {
        $query
            ->leftJoin('members', 'payments.member_id', '=', 'members.id')
            ->leftJoin('plans', 'members.plan_id', '=', 'plans.id')
            ->leftJoin('packages', 'members.package_id', '=', 'packages.id')
            ->leftJoin('programs', 'members.program_id', '=', 'programs.id');
    }

    public function tableQuery(Builder $query)
    {
        $query
            ->leftJoin('bookings', 'bookings.payment_id', '=', 'payments.id')
            ->leftJoin('membership_types', 'membership_types.id', '=', 'members.membership_type_id')
            ->select(
                'programs.id as program_id',
                'programs.name as program_name',
                'packages.id as package_id',
                'packages.title as package_title',
                'plans.id as plan_id',
                'plans.title as plan_title',
                'bookings.id as booking_id',
                'membership_types.title as membership_type_title',
                'membership_types.id as membership_type_id',
            )
            ->selectRaw(
                'CONCAT_WS(" ", members.first_name, members.last_name) as full_name, (payments.total_amount - payments.vat_amount) as total_amount_wo_vat'
            )
            ->with('member');
    }

    public function statusQuery(Builder $query)
    {
        $query
            ->leftJoin('bookings', 'bookings.payment_id', '=', 'payments.id')
            ->select(
                'members.id as program_id',
                'programs.id as program_id',
                'programs.name as program_name',
                'packages.id as package_id',
                'packages.title as package_title',
                'plans.id as plan_id',
                'plans.title as plan_title',
                'booking_payment.*',
                'bookings.id as booking_id',
            );
    }

    public function fields(): array
    {
        return [
            Select::make('status')
                ->options(Payment::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            BelongsTo::make('zohoInvoice', ZohoInvoice::class, null, 'Zoho invoice #')
                ->titleField('invoice_number')
                ->url(fn ($record) => $record->zohoInvoice?->urlToAdmin)
                ->hasAccess(fn () => Auth::user()->hasTeam('adv_management'))
                ->onlyOnTable(),
            Text::make('reference_id', 'Booking ID')
                ->url(
                    fn ($record) => \PrslV2::checkGatePolicy(
                        'view',
                        Booking::class,
                        $record
                    ) ? ('/bookings/'.$record->booking_id.'/view') : null
                )
                ->sortable(),
            BelongsTo::make('member', MemberPrimary::class, null, 'Member ID')
                ->url($this->memberUrlCallback())
                ->titleField('member_id')
                ->sortable(),
            Text::make('full_name', 'Member Full Name')
                ->column('member_id')
                ->url(
                    \PrslV2::checkGatePolicy(
                        'index',
                        Member::class
                    ) ? '/member-primary/{member_id}' : null
                )
                ->onlyOnTable()
                ->displayHandler(fn ($record) => $record->full_name),
            Money::make('subtotal_amount', 'Subtotal amount')
                ->tooltip('Subtotal amount (before discount)')
                ->rules('required'),
            Money::make('discount_amount')
                ->rules('required'),
            Money::make('total_amount_without_vat')
                ->onlyOnTable(),
            Money::make('vat_amount')
                ->onlyOnTable(),
            Money::make('total_amount')
                ->hideOnTable()
                ->onlyOnTable(),
            Money::make('third_party_commission_amount', '3rd party commission')
                ->default(0)
                ->hideOnTable()
                ->sortable(),
            Money::make('refund_amount')
                ->hideOnTable()
                ->onlyOnTable(),
            BelongsTo::make('paymentMethod', PaymentMethod::class, null, 'Payment Method')
                ->url(
                    \PrslV2::checkGatePolicy(
                        'view',
                        PaymentMethod::class
                    ) ? '/payment-methods/{payment_method_id}' : null
                )
                ->tooltip(
                    \PrslV2::checkGatePolicy(
                        'index',
                        PaymentMethod::class
                    ) ? 'Auto updates to payment method of the last transaction' : null
                )
                ->rules('required')
                ->sortable(),
            BelongsTo::make('paymentType', PaymentType::class, null, 'Payment Type')
                ->url('/payment-types/{payment_type_id}')
                ->hasAccess(fn () => \PrslV2::checkGatePolicy('index', PaymentType::class))
                ->rules('required')
                ->sortable(),
            Text::make('offer_code')
                ->hideOnTable(),

            Date::make('payment_date')
                ->default(now())
                ->rules('required')
                ->sortable(),

            DateTime::make('created_at')
                ->onlyOnTable()
                ->hideOnTable()
                ->sortable(),

            Text::make('plan_title', 'Plan')
                ->computed()
                ->url(
                    \PrslV2::checkGatePolicy(
                        'view',
                        Plan::class
                    ) ? '/payment-plans/{plan_id}' : null
                )
                ->onlyOnTable()
                ->displayHandler(fn ($record) => $record->plan_title)
                ->hideOnTable(),

            Text::make('package_title', 'Package')
                ->computed()
                ->url('/packages/{package_id}')
                ->onlyOnTable()
                ->hasAccess(fn () => \PrslV2::checkGatePolicy('index', Package::class))
                ->displayHandler(fn ($record) => $record->package_title)
                ->hideOnTable(),

            Text::make('program_title', 'Program')
                ->computed()
                ->url('/programs/{program_id}')
                ->onlyOnTable()
                ->hasAccess(fn () => \PrslV2::checkGatePolicy('index', Program::class))
                ->displayHandler(fn ($record) => $record->program_name)
                ->hideOnTable(),

            Text::make('membership_type_title', 'Membership Type')
                ->computed()
                ->url(
                    \PrslV2::checkGatePolicy(
                        'view',
                        MembershipType::class
                    ) ? '/membership-type/{membership_type_id}' : null
                )
                ->onlyOnTable()
                ->displayHandler(fn ($record) => $record->membership_type_title)
                ->hideOnTable(),

            HasMany::make('files', PaymentFile::class, 'files', 'Attachment files')
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
                'status',
                'zohoInvoice',
                'reference_id',
                'member_id',
                'subtotal_amount',
                'discount_amount',
                'third_party_commission_amount',
                'member',
                'paymentMethod',
                'paymentType',
                'offer_code',
                'payment_date',
            ]),
            Group::make('files')->attach([
                'files',
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            EqualFilter::make(TextFilterField::make(), 'member_id', 'members.id')
                ->hidden(),

            InFilter::make(
                (new MultipleSelectFilterField())->options(Payment::STATUSES),
                'status',
                'payments.status'
            )->quick(),
            LikeFilter::make(new TextFilterField(), 'member', 'members.member_id')
                ->quick(),
            LikeFilter::make(new TextFilterField(), 'email', 'members.email')
                ->quick(),
            LikeFilter::make(new TextFilterField(), 'reference_id', 'payments.reference_id', 'Booking ID')
                ->quick(),
            BetweenFilter::make(
                new DateFilterField(),
                new DateFilterField(),
                'payment_date',
                'payments.payment_date',
            )->quick(),

            InFilter::make(
                (new MultipleSelectFilterField())->endpoint('payment/relation-options/paymentMethod'),
                'paymentMethod',
                'paymentMethod.id'
            )->hasAccess(fn () => !auth()->user()->program_id),
            InFilter::make(
                (new MultipleSelectFilterField())->endpoint('payment/relation-options/paymentType'),
                'paymentType',
                'paymentType.id'
            )->hasAccess(fn () => !auth()->user()->program_id),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options($this->getPlans()),
                'plans.id',
                'plans.id',
                'Plan'
            ),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options($this->getPackages()),
                'packages.id',
                'packages.id',
                'Package'
            )->hasAccess(fn () => !auth()->user()->program_id),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Program::getSelectable()),
                'programs.id',
                'programs.id',
                'Program'
            )->hasAccess(fn () => !auth()->user()->program_id),

            LikeFilter::make(new TextFilterField(), 'offer_code', 'payments.offer_code')
                ->hasAccess(fn () => !auth()->user()->program_id),
        ];
    }

    public function layoutFilters(): array
    {
        return [
            Group::make('')->attach([
                'status',
                'member',
                'email',
                'paymentMethod',
                'paymentType',
                'reference_id',
                'offer_code',
                'payment_date',
                'plans.id',
                'packages.id',
                'programs.id',
            ]),
        ];
    }

    public function statuses(): array
    {
        return [
            DoughnutStatus::make('Statuses')
                ->count('payments.status')
                ->labels(Payment::getConstOptions('STATUSES'))
                ->colors(self::STATUS_BADGES),
            TextStatus::make(
                'Gross Sales',
                fn ($query) => money_formatter(
                    $query->sum(
                        DB::raw(
                            'payments.total_amount + payments.discount_amount'
                        )
                    )
                )
            )->hint('(Incl.: VAT, 3rd party Commission; before Discounts, before Refund)')
                ->hasAccess(fn () => !auth()->user()->program_id),

            TextStatus::make(
                'Total Amount of Discounts Given',
                fn ($query) => money_formatter($query->sum('discount_amount'))
            )
                ->hasAccess(fn () => !auth()->user()->program_id),

            TextStatus::make(
                'Total Paid',
                fn ($query) => money_formatter(
                    $query->sum(
                        DB::raw(
                            'payments.total_amount'
                        )
                    )
                )
            )->hint('(Incl VAT, commision, after discount)')
                ->hasAccess(fn () => !auth()->user()->program_id),

            TextStatus::make(
                'Total VAT Value',
                fn ($query) => money_formatter($query->sum('payments.vat_amount'))
            )->hasAccess(fn () => !auth()->user()->program_id),

            TextStatus::make(
                'Total Refund Value',
                fn ($query) => money_formatter($query->sum('payments.refund_amount'))
            )->hasAccess(fn () => !auth()->user()->program_id),

            TextStatus::make(
                'Total 3rd Party Commission Value',
                fn ($query) => money_formatter($query->sum('payments.third_party_commission_amount'))
            )->hint('(Agreed % of sales after VAT, discount, refund)')
                ->hasAccess(fn () => !auth()->user()->program_id),

            TextStatus::make(
                'Adjusted Sales',
                fn ($query) => money_formatter(
                    $query->sum(
                        DB::raw(
                            'payments.total_amount_without_vat + payments.discount_amount - payments.refund_amount'
                        )
                    )
                )
            )->hint('(Sales after: discount and VAT, refund)')
                ->hasAccess(fn () => !auth()->user()->program_id),
            TextStatus::make(
                'NET SALES',
                fn ($query) => money_formatter(
                    $query->sum(
                        DB::raw('payments.total_amount - payments.vat_amount - payments.third_party_commission_amount')
                    )
                )
            )->hint('(Sales after: discounts, VAT, refund, 3rd party Commission)')
                ->hasAccess(fn () => !auth()->user()->program_id),

        ];
    }

    protected function getPackages(): array
    {
        return Package::orderBy('title')
            ->pluck('title', 'id')
            ->toArray();
    }

    protected function getPlans(): array
    {
        return Plan::orderBy('title')
            ->pluck('title', 'id')
            ->toArray();
    }

    protected function memberUrlCallback(): callable
    {
        return function ($record) {
            if (\PrslV2::checkGatePolicy('index', Member::class) && $member = $record->member) {
                if ($member->member_type == 'member') {
                    return "/member-primary/{$member->id}";
                }
                if ($member->member_type == 'partner') {
                    return "/member-primary/{$member->parent_id}/member-partner/{$member->id}";
                }
                if ($member->member_type == 'junior') {
                    return "/member-primary/{$member->parent_id}/junior/{$member->id}";
                }
            }
        };
    }
}
