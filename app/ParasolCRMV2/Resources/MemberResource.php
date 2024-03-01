<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\BackofficeUser;
use App\Models\Club\Club;
use App\Models\Corporate;
use App\Models\Member\Member;
use App\Models\Member\MembershipDuration;
use App\Models\Member\MembershipSource;
use App\Models\Member\MembershipType;
use App\Models\Package;
use App\Models\Plan;
use App\Models\Program;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Containers\HorizontalTab;
use ParasolCRMV2\Containers\TabElement;
use ParasolCRMV2\Fields\BelongsTo;
use ParasolCRMV2\Fields\Date;
use ParasolCRMV2\Fields\Email;
use ParasolCRMV2\Fields\Hidden;
use ParasolCRMV2\Fields\HorizontalRadioButton;
use ParasolCRMV2\Fields\Phone;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\BetweenFilter;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\SelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;
use ParasolCRMV2\Statuses\DoughnutStatus;

class MemberResource extends ResourceScheme
{
    public static $model = Member::class;

    public static $defaultSortBy = 'start_date';

    public const MEMBERSHIP_STATUS_BADGES = [
        'active' => 'green',
        'expired' => 'gray',
        'cancelled' => 'orange',
        'redeemed' => 'gray',
        'processing' => 'blue',
        'transferred' => 'blue',
        'paused' => 'red',
        'payment_defaulted_on_hold' => 'blue',
    ];

    public const MEMBER_TYPE_BADGES = [
        'member' => 'green',
        'partner' => 'blue',
        'junior' => 'orange',
    ];

    public function query(Builder $query)
    {
        $query->leftJoin('checkins', 'members.id', '=', 'checkins.member_id')
            ->leftJoin('member_club_favorite', 'members.id', '=', 'member_club_favorite.member_id')
            ->leftJoin('member_membership_duration', 'members.id', '=', 'member_membership_duration.member_id')
            ->leftJoin('member_club', 'members.id', '=', 'member_club.member_id');
    }

    public function tableQuery(Builder $query)
    {
        $query->with('membershipDurations', 'clubs', 'area.city', 'favoriteClubs', 'pendingMembershipRenewal');
    }

    public function fields(): array
    {
        return [
            Text::make('parent_id', 'Primary member ID')
                ->hideOnTable(),
            HorizontalRadioButton::make('member_type')
                ->options(Member::getConstOptions('MEMBER_TYPES'))
                ->badges(self::MEMBER_TYPE_BADGES)
                ->sortable(),
            BelongsTo::make('program', Program::class)
                ->titleField('name')
                ->url('/programs/{program_id}')
                ->sortable(),
            BelongsTo::make('package', Package::class)
                ->url('/packages/{package_id}')
                ->sortable(),
            BelongsTo::make('plan', Package::class)
                ->url('/plans/{plan_id}')
                ->sortable(),
            Text::make('member_id')
                ->sortable(),
            Select::make('membership_status')
                ->options(Member::getConstOptions('membership_statuses'))
                ->badges(static::MEMBERSHIP_STATUS_BADGES)
                ->sortable(),
            Date::make('start_date', 'Join date')
                ->sortable(),
            Date::make('end_date', 'Expiry')
                ->sortable(),
            Text::make('full_name', 'Full name')
                ->computed()
                ->setFromRecordHandler(function ($values) {
                    return $values['first_name'].' '.$values['last_name'];
                })
                ->unfillableRecord(),
            Text::make('first_name')
                ->hideOnTable(),
            Text::make('last_name')
                ->hideOnTable(),
            Date::make('dob', 'Date of birth')
                ->hideOnTable(),
            Select::make('membership_type_id', 'Membership type')
                ->options($this->getMembershipTypes())
                ->sortable(),
            Email::make('email', 'Personal email')
                ->hideOnTable(),
            Phone::make('phone', 'Personal phone')
                ->hideOnTable(),
            Email::make('recovery_email', 'Recovery email')
                ->hideOnTable(),
            Email::make('login_email', 'Login email')
                ->hideOnTable(),
            Text::make('membershipDurations', 'Membership durations')
                ->computed()
                ->displayHandler(function ($record) {
                    $membershipDurations = $record->membershipDurations->pluck('title')->toArray();

                    return implode(', ', $membershipDurations);
                })
                ->hideOnTable(),
            BelongsTo::make('corporate', Corporate::class)
                ->hideOnTable()
                ->sortable(),
            Text::make('clubs')
                ->computed()
                ->displayHandler(function ($record) {
                    $clubs = $record->clubs->pluck('title')->toArray();

                    return implode(', ', $clubs);
                })
                ->hideOnTable(),
            Text::make('favoriteClubs')
                ->computed()
                ->displayHandler(function ($record) {
                    $clubs = $record->favoriteClubs->pluck('title')->toArray();

                    return implode(', ', $clubs);
                })
                ->hideOnTable(),
            Text::make('location')
                ->computed()
                ->hideOnTable()
                ->onlyOnTable(),
            Hidden::make('renewal_url')
                ->displayHandler(function (Member $record) {
                    return $record->pendingMembershipRenewal?->renewal_url ?? ($record->member_type == Member::MEMBER_TYPES['member'] ? '' : null);
                }),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'members.member_id', null, 'Member id')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'first_name')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'last_name')
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Member::getConstOptions('MEMBER_TYPES')),
                'member_type',
                'member_type',
                'Member type'
            )
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Program::getSelectable()),
                'program_id',
                'members.program_id',
                'Program'
            )
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options($this->getPackages()),
                'package_id',
                'members.package_id',
                'Package'
            ),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options($this->getPlans()),
                'plan_id',
                'members.plan_id',
                'Plan'
            ),
            EqualFilter::make(
                SelectFilterField::make()->options(Plan::getConstOptions('allowed_club_types')),
                'allowed_club_type',
                'plan.allowed_club_type',
                'Plan allowed club type'
            ),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Member::getConstOptions('membership_statuses')),
                'membership_status'
            ),
            BetweenFilter::make(DateFilterField::make(), DateFilterField::make(), 'start_date'),
            BetweenFilter::make(DateFilterField::make(), DateFilterField::make(), 'end_date'),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options($this->getMembershipTypes()),
                'membership_type_id',
                'members.membership_type_id',
                'Membership type'
            ),
            LikeFilter::make(TextFilterField::make(), 'login_email'),
            LikeFilter::make(TextFilterField::make(), 'email', 'members.email'),
            LikeFilter::make(TextFilterField::make(), 'recovery_email'),
            LikeFilter::make(TextFilterField::make(), 'phone'),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options($this->getMembershipSources()),
                'membership_source_id',
                'members.membership_source_id',
                'Membership source'
            ),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Corporate::getSelectable()),
                'corporate_id',
                'corporate_id',
                'Corporate'
            ),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(BackofficeUser::getSelectable()),
                'bdm',
                'bdm_backoffice_user_id'
            )
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(MembershipDuration::getSelectable()),
                'membershipDurations',
                'member_membership_duration.membership_duration_id',
                'Membership durations'
            ),
            LikeFilter::make(TextFilterField::make(), 'referral_code'),
            LikeFilter::make(TextFilterField::make(), 'offer_code'),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Club::getSelectable()),
                'clubs',
                'member_club.club_id',
                'Clubs'
            ),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Club::getSelectable()),
                'checkin_clubs',
                'checkins.club_id',
            ),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Club::getSelectable()),
                'favorite_clubs',
                'member_club_favorite.club_id',
            ),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'checkin_date',
                'checkins.checked_in_at'
            ),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(CheckinResource::STATUSES),
                'checkin_status',
                'checkins.club_id',
            ),
        ];
    }

    public function layoutFilters(): array
    {
        return [
            HorizontalTab::make()->attach([
                TabElement::make('Basic information')->attach([
                    'member_type',
                    'members.member_id',
                    'membership_status',
                    'start_date',
                    'end_date',
                    'first_name',
                    'last_name',
                    'dob',
                    'membership_type_id',
                    'login_email',
                    'email',
                    'recovery_email',
                    'phone',
                    'membership_source_id',
                    'membershipDurations',
                    'corporate_id',
                    'bdm',
                ]),
                TabElement::make('Program, package, plan & clubs')->attach([
                    'program_id',
                    'package_id',
                    'plan_id',
                    'allowed_club_type',
                    'clubs',
                    'favorite_clubs',
                ]),
                TabElement::make('Club visits')->attach([
                    'checkin_clubs',
                    'checkin_date',
                    'checkin_status',
                ]),
                TabElement::make('Referral & offer codes')->attach([
                    'referral_code',
                    'offer_code',
                ]),
            ]),
        ];
    }

    public function statuses(): array
    {
        return [
            DoughnutStatus::make('Member types')
                ->count('members.member_type')
                ->labels(Member::getConstOptions('MEMBER_TYPES'))
                ->colors(self::MEMBER_TYPE_BADGES),
            DoughnutStatus::make('Primary members statuses')
                ->query(function ($builder) {
                    return $builder->where('members.member_type', Member::MEMBER_TYPES['member']);
                })
                ->count('members.membership_status')
                ->labels(Member::getConstOptions('membership_statuses'))
                ->colors(self::MEMBERSHIP_STATUS_BADGES),
            DoughnutStatus::make('Partners statuses')
                ->query(function ($builder) {
                    return $builder->where('members.member_type', Member::MEMBER_TYPES['partner']);
                })
                ->count('members.membership_status')
                ->labels(Member::getConstOptions('membership_statuses'))
                ->colors(self::MEMBERSHIP_STATUS_BADGES),
            DoughnutStatus::make('Juniors statuses')
                ->query(function ($builder) {
                    return $builder->where('members.member_type', Member::MEMBER_TYPES['junior']);
                })
                ->count('members.membership_status')
                ->labels(Member::getConstOptions('membership_statuses'))
                ->colors(self::MEMBERSHIP_STATUS_BADGES),
        ];
    }

    public static function label(): string
    {
        return 'All Members';
    }

    public static function singularLabel(): string
    {
        return 'All Members';
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

    protected function getMembershipTypes(): array
    {
        return MembershipType::orderBy('title')
            ->pluck('title', 'id')
            ->toArray();
    }

    protected function getMembershipSources(): array
    {
        return MembershipSource::sort()
            ->pluck('title', 'id')
            ->toArray();
    }
}
