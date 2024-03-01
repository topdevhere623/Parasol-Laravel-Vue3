<?php

namespace App\ParasolCRM\Resources;

use App\Models\Booking;
use App\Models\Member\Member;
use App\Models\Member\MembershipRenewal;
use App\Models\Package;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\Boolean;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\Email;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\BetweenFilter;
use ParasolCRM\Filters\Fields\DateFilterField;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;
use ParasolCRM\Statuses\DoughnutStatus;

class MembershipRenewalResource extends ResourceScheme
{
    public static string $model = MembershipRenewal::class;

    public static string $defaultSortBy = 'end_date';

    public static string $defaultSortDirection = 'DESC';

    public const STATUS_BADGES = [
        'pending' => 'orange',
        'awaiting_due_date' => 'blue',
        'completed' => 'green',
    ];

    public function tableQuery(Builder $query)
    {
        $query->with('booking', 'member')
            ->select('member.email')
            ->leftJoinRelationship('member');
    }

    public function fields(): array
    {
        return [

            BelongsTo::make('member', Member::class, null, 'Membership #')
                ->url('/member-primary/{member_id}')
                ->onlyOnTable()
                ->sortable()
                ->titleField('member_id'),
            Email::make('email', 'Email')
                ->onlyOnTable()
                ->sortable('member.email'),

            BelongsTo::make('booking', Booking::class)
                ->label('Booking ID')
                ->titleField('reference_id')
                ->sortable('booking.reference_id')
                ->url(
                    fn ($record) => $record->booking && \Prsl::checkGatePolicy(
                        'view',
                        Booking::class,
                        $record->booking
                    )
                        ? ('/bookings/'.$record->booking_id.'/view')
                        : null
                ),

            HorizontalRadioButton::make('status')
                ->options(MembershipRenewal::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->sortable(),

            BelongsTo::make('oldPlan', Plan::class)
                ->label('Renewable Plan')
                ->hideOnTable()
                ->onlyOnTable()
                ->sortable('oldPlan.title')
                ->url(
                    fn ($record) => \Prsl::checkGatePolicy('update', Plan::class, $record)
                        ? ('/plans/'.$record->old_plan_id)
                        : null
                ),
            BelongsTo::make('newPlan', Plan::class)
                ->label('Renewed Plan')
                ->hideOnTable()
                ->onlyOnTable()
                ->sortable('newPlan.title')
                ->url(
                    fn ($record) => \Prsl::checkGatePolicy('update', Plan::class, $record)
                        ? ('/plans/'.$record->new_plan_id)
                        : null
                ),

            BelongsTo::make('renewalPackage', Package::class)
                ->label('Renewable Package')
                ->hideOnTable()
                ->optionHandler(fn ($builder) => $builder->active())
                ->sortable('renewalPackage.title')
                ->url(
                    fn ($record) => \Prsl::checkGatePolicy('update', Package::class, $record)
                        ? ('/plans/'.$record->renewal_package_id)
                        : null
                ),

            Date::make('due_date', 'Due Date')
                ->sortable(),
            Date::make('end_date', 'Expiry Date')
                ->onlyOnTable()
                ->computed(),
            Boolean::make('is_30_days_email_sent', '30d email sent')
                ->onlyOnTable()
                ->sortable(),
            Boolean::make('is_7_days_email_sent', '7d email sent')
                ->onlyOnTable()
                ->sortable(),
            Boolean::make('is_expired_email_sent', 'expired email sent')
                ->onlyOnTable()
                ->sortable(),
            Boolean::make('is_7_days_expired_email_sent', '7d expired email sent')
                ->onlyOnTable()
                ->sortable(),

            Text::make('Link')
                ->onlyOnTable()
                ->displayHandler(fn ($record) => 'Link')
                ->url(fn ($record) => $record->renewal_url)
                ->badges(['default' => 'green'])
                ->onlyOnTable(),

        ];
    }

    public function filters(): array
    {
        return [
            InFilter::make(
                MultipleSelectFilterField::make()->options(MembershipRenewal::getConstOptions('statuses')),
                'status',
                'membership_renewals.status'
            )->quick(),
            LikeFilter::make(TextFilterField::make(), 'membership_number', 'member.member_id')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'email', 'member.login_email')
                ->quick(),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'end_date',
                'membership_renewals.end_date',
                'Expiry date'
            )
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'booking_id', 'booking.reference_id', 'Booking ID'),
        ];
    }

    public function statuses(): array
    {
        return [
            DoughnutStatus::make('Statuses')
                ->count('membership_renewals.status')
                ->labels(MembershipRenewal::getConstOptions('statuses'))
                ->colors(self::STATUS_BADGES),
        ];
    }
}
