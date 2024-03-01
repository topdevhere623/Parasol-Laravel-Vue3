<?php

namespace App\ParasolCRM\Resources;

use App\Models\Club\Checkin;
use App\Models\Member\Member;
use App\Models\Plan;
use App\Models\Reports\HSBCClubVisits;
use App\Scopes\HSBCComplimentaryPlanScope;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\BelongsToMany;
use ParasolCRM\Fields\DateTime;
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

class HsbcClubVisitResource extends ResourceScheme
{
    public static string $model = HSBCClubVisits::class;

    public const STATUS_BADGES = [
        'completed' => 'green',
        'cancelled' => 'red',
        'refunded' => 'orange',
        'unknown' => 'default',
    ];

    public function tableQuery(Builder $query)
    {
        $query->with('plan', 'booking.hsbcUsedCard');
        (new HSBCComplimentaryPlanScope())->apply($query, $query->getModel());
    }

    public function fields(): array
    {
        return [
            Text::make('member_id', 'Soleil Member #')
                ->url(
                    fn ($record) => \Prsl::checkGatePolicy(
                        'update',
                        Member::class,
                        $record
                    ) ? ('/member-primary/'.$record->member_id) : null
                )
                ->sortable(),
            HorizontalRadioButton::make('membership_status')
                ->options(Member::getConstOptions('membership_statuses'))
                ->badges(MemberResource::MEMBERSHIP_STATUS_BADGES),
            HorizontalRadioButton::make('member_type')
                ->options(Member::getConstOptions('MEMBER_TYPES'))
                ->badges(MemberResource::MEMBER_TYPE_BADGES)
                ->hideOnTable(),
            Text::make('first_name')
                ->sortable(),
            Text::make('last_name')
                ->sortable(),
            Text::make('phone')
                ->sortable(),
            Email::make('login_email', 'Email')
                ->sortable(),
            BelongsTo::make('plan', Plan::class, 'plan', 'Product type')
                ->url(
                    fn ($record) => \Prsl::checkGatePolicy('update', Plan::class, $record->plan)
                        ? ('/plans/'.$record->plan_id)
                        : null
                )
                ->sortable(),
            DateTime::make('start_date', 'Start date')
                ->hideOnTable(),
            BelongsToMany::make('checkins', Checkin::class, 'checkins', 'Club check-ins total')
                ->sortable()
                ->onlyOnTable(),
            Text::make('card_last4_digits')
                ->computed()
                ->displayHandler(fn ($model) => optional(optional($model->booking)->hsbcUsedCard)->card_last4_digits),
        ];
    }

    public function filters(): array
    {
        return [
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'checked_date',
                'checkins.checked_in_at',
                'Check-in Date'
            )
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Member::getConstOptions('membership_statuses')),
                'membership_status'
            ),
            LikeFilter::make(TextFilterField::make(), 'members.first_name', null, 'First name')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'members.last_name', null, 'Last name')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'members.phone', 'members.phone', 'Phone')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'login_email', 'login_email', 'Email')
                ->quick(),
        ];
    }

    public static function label()
    {
        $isAdmin = auth()->user()->isAdmin();
        return $isAdmin ? 'Reports | HSBC Club Visits' : 'Club Visits';
    }
}
