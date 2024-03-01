<?php

namespace App\ParasolCRM\Resources;

use App\Models\Club\Checkin;
use App\Models\Member\Member;
use App\Models\Plan;
use App\Models\Reports\ProgramReportMember;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\Email;
use ParasolCRM\Fields\HorizontalRadioButton;
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

class ReportProgramMemberResource extends ResourceScheme
{
    public static string $model = ProgramReportMember::class;

    public function tableQuery(Builder $query)
    {
        $query->with('plan')->withCount([
            'checkins as visits_count' => function (Builder $query) {
                $query->whereIn('status', [Checkin::STATUSES['checked_in'], Checkin::STATUSES['checked_out']]);
            },
        ]);
    }

    public function fields(): array
    {
        return [
            Text::make('member_id', 'Membership #')
                ->sortable(),
            Select::make('membership_status')
                ->options(Member::getConstOptions('membership_statuses'))
                ->badges(MemberResource::MEMBERSHIP_STATUS_BADGES)
                ->sortable(),
            HorizontalRadioButton::make('member_type')
                ->options(Member::getConstOptions('MEMBER_TYPES'))
                ->badges(MemberResource::MEMBER_TYPE_BADGES)
                ->sortable(),
            Text::make('first_name')
                ->sortable(),
            Text::make('last_name')
                ->sortable(),
            Email::make('login_email', 'Email')
                ->sortable(),
            Date::make('start_date', 'Join date')
                ->sortable(),
            Date::make('end_date', 'Expiry')
                ->sortable(),
            Text::make('visits_count', 'Number of visits')
                ->sortable('visits_count'),
            BelongsTo::make('plan', Plan::class, 'plan', 'Plan')
                ->url(
                    fn ($record) => \Prsl::checkGatePolicy('update', Plan::class, $record->plan)
                        ? ('/plans/'.$record->plan_id)
                        : null
                )
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Member::getConstOptions('membership_statuses')),
                'membership_status',
                'members.membership_status'
            ),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Member::getConstOptions('MEMBER_TYPES')),
                'member_type',
                'member_type',
                'Member type'
            )
                ->quick(),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'members.start_date',
                null,
                'Join date'
            )
                ->quick(),
            BetweenFilter::make(DateFilterField::make(), DateFilterField::make(), 'members.end_date', null, 'Expiry')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'members.first_name', null, 'First name')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'members.last_name', null, 'Last name')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'members.phone', 'members.phone', 'Phone')
                ->hasAccess(fn () => \Prsl::checkGatePolicy('index', Member::class))
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'login_email', 'login_email', 'Email')
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Plan::getSelectable()),
                'plan_id',
                'members.plan_id',
                'Plan'
            ),
        ];
    }

    public function statuses(): array
    {
        return [
            DoughnutStatus::make('Member types')
                ->count('members.member_type')
                ->labels(Member::getConstOptions('MEMBER_TYPES'))
                ->colors(MemberResource::MEMBER_TYPE_BADGES),
            DoughnutStatus::make('Statuses')
                ->count('members.membership_status')
                ->labels(Member::getConstOptions('membership_statuses'))
                ->colors(MemberResource::MEMBERSHIP_STATUS_BADGES),
        ];
    }

    public static function label()
    {
        $isAdmin = auth()->user()->isAdmin();
        return $isAdmin ? 'Reports | Program Members' : 'Members';
    }

}
