<?php

namespace App\ParasolCRM\Resources;

use App\Enum\Booking\StepEnum;
use App\Models\BackofficeUser;
use App\Models\Booking;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadTag;
use App\Models\Member\Member;
use App\Models\Member\MembershipSource;
use App\Models\Package;
use App\Models\Payments\PaymentMethod;
use App\Models\Plan;
use App\Models\Program;
use App\ParasolCRM\Filters\BookingExcludeTagFilter;
use App\Scopes\SalesLeadScope;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\DateTime;
use ParasolCRM\Fields\Email;
use ParasolCRM\Fields\Hidden;
use ParasolCRM\Fields\Money;
use ParasolCRM\Fields\Number;
use ParasolCRM\Fields\Phone;
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

class BookingResource extends ResourceScheme
{
    public static $model = Booking::class;

    // @TODO: после перехода на php8.2, использовать enum->value
    public const STEPS = [
        'payment' => 'Payment',
        'membership_details' => 'Membership Details',
        'completed' => 'Complete',
    ];

    public const STEPS_BADGES = [
        'completed' => 'green',
        'default' => 'orange',
    ];

    public const TYPES_BADGES = [
        'booking' => 'green',
        'renewal' => 'blue',
    ];

    public function tableQuery(Builder $query)
    {
        $query->selectRaw(
            'programs.name as program, programs.id as program_id, plans.title as plan_title, leads.nocrm_id, payments.total_amount as paid_amount'
        )
            ->leftJoin('plans', 'bookings.plan_id', 'plans.id')
            ->leftJoin('packages', 'plans.package_id', 'packages.id')
            ->leftJoin('programs', 'packages.program_id', 'programs.id')
            ->leftJoin('leads', 'bookings.lead_id', 'leads.id')
            ->leftJoin('lead_lead_tag', 'lead_lead_tag.lead_id', '=', 'leads.id')
            ->leftJoin('backoffice_users', 'leads.backoffice_user_id', 'backoffice_users.id')
            ->leftJoin('payments', 'bookings.payment_id', 'payments.id')
            ->with('member')
            ->with(
                'lead',
                fn ($query) => $query->with('backofficeUser', 'leadTags')->withoutGlobalScope(SalesLeadScope::class)
            );
    }

    public function statusQuery(Builder $query)
    {
        $this->tableQuery($query);
    }

    public function fields(): array
    {
        return [
            Select::make('step')
                ->options(self::STEPS)
                ->badges(self::STEPS_BADGES)
                ->onlyOnTable()
                ->sortable(),
            Text::make('reference_id', 'Booking ID')
                ->url(
                    fn ($record) => \Prsl::checkGatePolicy(
                        'view',
                        Booking::class,
                        $record
                    ) ? ('/bookings/'.$record->id.'/view') : null
                )
                ->onlyOnTable()
                ->sortable(),
            BelongsTo::make('member', Member::class, 'member')
                ->titleField('member_id')
                ->onlyOnTable()
                ->hideOnTable()
                ->hasAccess(fn () => \Prsl::checkGatePolicy('index', Member::class))
                ->url(
                    fn ($record) => $record->member_id && \Prsl::checkGatePolicy('view', Member::class, $record->member)
                        ? ('/member-primary/'.$record->member_id)
                        : null
                )
                ->sortable('member.member_id'),
            Text::make('program')
                ->hasAccess(fn () => \Prsl::checkGatePolicy('index', Program::class))
                ->onlyOnTable()
                ->url('/programs/{program_id}'),
            Text::make('plan_title', 'Plan')
                ->onlyOnTable()
                ->url(
                    fn ($record) => \Prsl::checkGatePolicy('index', Program::class) ? '/plans/'.$record->plan_id : null
                ),
            Text::make('name', 'Name')
                ->sortable(),
            Email::make('email')
                ->sortable(),
            Phone::make('phone')
                ->sortable(),
            Number::make('units')
                ->onlyOnTable()
                ->hideOnTable()
                ->hasAccess(fn () => auth()->user()->hasTeam('adv_management'))
                ->sortable(),
            Money::make('total_price', 'Booking Value')
                ->onlyOnTable()
                ->sortable(),
            BelongsTo::make('paymentMethod', PaymentMethod::class, null, 'Payment Method')
                ->url('/payment-methods/{payment_method_id}')
                ->onlyOnTable()
                ->sortable(),
            Money::make('paid_amount', 'Paid Amount')
                ->onlyOnTable()
                ->sortable('paid_amount'),
            Money::make('total_third_party_commission_amount', '3rd party commission')
                ->onlyOnTable()
                ->hideOnTable()
                ->sortable(),
            Select::make('type', 'Type')
                ->options(Booking::getConstOptions('types'))
                ->onlyOnTable()
                ->badges(self::TYPES_BADGES)
                ->sortable(),
            Text::make('backoffice_user_name', 'Sales Person')
                ->displayHandler(function (Booking $record) {
                    return $record->lead?->backofficeUser?->full_name;
                })
                ->url('https://adv.nocrm.io/leads/{nocrm_id}')
                ->onlyOnTable()
                ->hideOnTable()
                ->sortable('backoffice_users.first_name'),

            BelongsTo::make('lead', Lead::class)
                ->label('Lead')
                ->optionHandler(fn () => Lead::getSelectable())
                ->onlyOnForm(),
            Text::make('tags')
                ->computed()
                ->displayHandler(function (Booking $record) {
                    return $record->lead?->leadTags?->pluck('name')->implode(', ');
                })
                ->onlyOnTable()
                ->hideOnTable(),
            DateTime::make('created_at', 'First Step Date')
                ->onlyOnTable()
                ->column('created_at')
                ->sortable(),
            DateTime::make('last_step_changed_at', 'Last Step Date')
                ->onlyOnTable()
                ->column('last_step_changed_at')
                ->sortable(),
            // hidden field
            Hidden::make('continue_url')
                ->unfillableRecord()
                ->displayHandler(function (Booking $record) {
                    return $record->step != StepEnum::Completed ? route(
                        'booking.step-'.$record->step->getOldValue(),
                        $record->uuid
                    ) : null;
                }),
        ];
    }

    public function filters(): array
    {
        return [
            InFilter::make(
                (new MultipleSelectFilterField())
                    ->options(self::STEPS),
                'step',
                'bookings.step'
            )->quick(),
            LikeFilter::make(new TextFilterField(), 'email', 'bookings.email')
                ->quick(),
            LikeFilter::make(new TextFilterField(), 'phone', 'bookings.phone')
                ->quick(),
            LikeFilter::make(new TextFilterField(), 'name', 'bookings.name')
                ->quick(),
            LikeFilter::make(new TextFilterField(), 'reference_id', 'bookings.reference_id', 'Booking ID')
                ->quick(),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'created_at',
                'bookings.created_at',
                'First Step Date'
            ),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'last_step_changed_at',
                'bookings.last_step_changed_at',
                'Last Step Date'
            ),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Program::getSelectable()),
                'program',
                'programs.id',
                'Program'
            )
                ->hasAccess(fn () => \Prsl::checkGatePolicy('index', Program::class)),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Package::getSelectable()),
                'package',
                'packages.id'
            )
                ->hasAccess(fn () => \Prsl::checkGatePolicy('index', Package::class)),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Plan::getSelectable()),
                'plan',
                'plans.id'
            )
                ->hasAccess(fn () => \Prsl::checkGatePolicy('index', Plan::class)),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Booking::getConstOptions('types')),
                'type',
                'bookings.type',
            ),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(BackofficeUser::getSelectable()),
                'sales_person',
                'backoffice_users.id',
            )
                ->hasAccess(fn () => \Prsl::checkGatePolicy('index', BackofficeUser::class)),

            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(LeadTag::getSelectable()),
                'tags',
                'lead_lead_tag.lead_tag_id',
                'Lead tags'
            )
                ->hasAccess(fn () => \Prsl::checkGatePolicy('index', LeadTag::class)),

            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(LeadTag::getSelectable()),
                'include_tags',
                'lead_lead_tag.lead_tag_id',
                'Include lead tags'
            )
                ->hasAccess(fn () => \Prsl::checkGatePolicy('index', LeadTag::class)),
            BookingExcludeTagFilter::make(
                MultipleSelectFilterField::make()
                    ->options(LeadTag::getSelectable()),
                'exclude_tags',
                'lead_lead_tag.lead_tag_id',
                'Exclude lead tags'
            )
                ->hasAccess(fn () => \Prsl::checkGatePolicy('index', LeadTag::class)),

            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(PaymentMethod::getSelectable()),
                'payment_method',
                'payments.payment_method_id',
            )
                ->hasAccess(fn () => \Prsl::checkGatePolicy('index', PaymentMethod::class)),
        ];
    }

    public function statuses(): array
    {
        return [
            DoughnutStatus::make('Booking steps')
                ->query(fn ($q) => $q->whereIn(
                    'bookings.step',
                    [StepEnum::Completed, StepEnum::Payment, StepEnum::MembershipDetails]
                ))
                ->count('bookings.step')
                ->labels(self::STEPS)
                ->colors(self::STEPS_BADGES),
            DoughnutStatus::make('Membership source')
                ->hasAccess(fn () => !auth()->user()->program_id)
                ->labels($this->getMembershipStatusLabels())
                ->count('bookings.membership_source_id'),
            DoughnutStatus::make('Types')
                ->labels(Booking::getConstOptions('types'))
                ->colors(self::TYPES_BADGES)
                ->count('bookings.type'),
        ];
    }

    protected function getMembershipStatusLabels(): array
    {
        $data = MembershipSource::pluck('title', 'id')
            ->toArray();

        return $data + [null => 'Unknown'];
    }
}
