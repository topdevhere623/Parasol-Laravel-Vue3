<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Club\Checkin;
use App\Models\Club\Club;
use App\Models\Member\Member;
use App\Models\Plan;
use App\Models\Program;
use App\ParasolCRMV2\Filters\DuplicateCheckinsFilter;
use App\ParasolCRMV2\Filters\MemberParentFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Fields\Avatar;
use ParasolCRMV2\Fields\BelongsTo;
use ParasolCRMV2\Fields\DateTime;
use ParasolCRMV2\Fields\ID;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\BetweenFilter;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\Filters\Fields\HorizontalRadioButtonFilterField;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;
use ParasolCRMV2\Statuses\DoughnutStatus;
use ParasolCRMV2\Statuses\ProcessStatus;

class CheckinResource extends ResourceScheme
{
    public static $model = Checkin::class;

    public const STATUSES = [
        Checkin::STATUSES['checked_in'] => 'Checked-in',
        Checkin::STATUSES['checked_out'] => 'Checked-out',
        Checkin::STATUSES['paid_guest_fee'] => 'Paid guest fee',
        Checkin::STATUSES['turned_away'] => 'Turned away',
        Checkin::STATUSES['turned_away_expired'] => 'Turned away - Expired',
    ];

    public const STATUS_BADGES = [
        'checked_in' => 'green',
        'checked_out' => 'light',
        'paid_guest_fee' => 'orange',
        'turned_away' => 'red',
        'turned_away_expired' => 'red',
    ];

    public const TYPE_BADGES = [
        Checkin::TYPES['regular'] => 'green',
        Checkin::TYPES['class'] => 'blue',
    ];

    public function tableQuery(Builder $query)
    {
        $query->selectRaw('CONCAT_WS(" ", first_name, last_name) as full_name, member_type, member.avatar')
            ->join('clubs', 'clubs.id', '=', 'checkins.club_id')
            // TODO: refactor member avatar
            ->with('member', 'club');
    }

    public function fields(): array
    {
        return [
            ID::make()
                ->sortable()
                ->hasAccess(fn () => Auth::user()->isAdmin()),
            Avatar::make('avatar')
                ->displayHandler(fn ($record) => file_url($record->member, 'avatar', 'small'))
                ->username('full_name')
                ->onlyOnTable(),
            BelongsTo::make('member', Member::class, null, 'Member ID')
                ->url($this->memberUrlCallback())
                ->sortable()
                ->titleField('member_id'),
            Select::make('status')
                ->options(self::STATUSES)
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            Select::make('type')
                ->options(Checkin::getConstOptions('types'))
                ->badges(self::TYPE_BADGES)
                ->default(Checkin::TYPES['regular'])
                ->rules('required')
                ->hasAccess(fn () => !$this->isClubAdmin() || Auth::user()->club->hasClassesSlots())
                ->hideOnTable()
                ->sortable(),
            Text::make('number_of_kids', 'Kids')
                ->hasAccess(fn () => !Auth::user()->hasTeam('program_admins'))
                ->onlyOnTable()
                ->sortable(),
            Text::make('full_name', 'Member')
                ->column('member_id')
                ->onlyOnTable()
                ->displayHandler(fn ($record) => $record->full_name)
                ->url($this->memberUrlCallback()),
            BelongsTo::make('club', Club::class)
                ->hasAccess(fn () => Auth::user()->hasTeam(['adv_management', 'program_admins']))
                ->url(
                    fn ($record) => \PrslV2::checkGatePolicy(
                        'update',
                        Club::class,
                        $record->club
                    ) ? ('/clubs/'.$record->club_id) : null
                )
                ->sortable(),
            DateTime::make('checked_in_at', 'Checked-in')
                ->sortable(),
            DateTime::make('checked_out_at', 'Checked-out')
                ->sortable(),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('')->attach([
                'status',
                'member',
                'club',
                'checked_in_at',
                'checked_out_at',
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(new TextFilterField(), 'member', 'member.member_id')
                ->hasAccess(!$this->isClubAdmin())
                ->quick(),
            InFilter::make(
                (new MultipleSelectFilterField())->endpoint('checkin/filter/club-options'),
                'club',
                'club.id'
            )->hasAccess(!$this->isClubAdmin())
                ->quick(),
            InFilter::make(
                (new MultipleSelectFilterField())->options(self::STATUSES),
                'status',
                'checkins.status'
            )->hasAccess(!$this->isClubAdmin())
                ->quick(),
            InFilter::make(
                (new MultipleSelectFilterField())->options(Checkin::getConstOptions('types')),
                'type',
                'checkins.type'
            )->hasAccess(!$this->isClubAdmin()),
            BetweenFilter::make(
                new DateFilterField(now()),
                new DateFilterField(now()),
                'checked_in_at',
                null,
                'Checked-in date'
            )->quick(),

            // Required for search check-ins by member id or his parent id (used on member form)
            MemberParentFilter::make(new TextFilterField(), 'member_parent', 'member'),

            DuplicateCheckinsFilter::make(
                HorizontalRadioButtonFilterField::make(0)
                    ->defaultOptions([0 => 'No'])
                    ->options([1 => 'Yes']),
                'Daily duplicate checkins',
                'multi_checkin_id',
            )->hasAccess(!$this->isClubAdmin()),

            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Program::getSelectable()),
                'program',
                'member.program_id',
            )->hasAccess(!$this->isClubAdmin()),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Program::getSelectable()),
                'package',
                'member.package_id',
            )->hasAccess(!$this->isClubAdmin()),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Plan::getSelectable()),
                'plan',
                'member.plan_id',
            )->hasAccess(!$this->isClubAdmin()),

        ];
    }

    public function statuses(): array
    {
        /** @var Club $club */
        $club = Auth::user()->club;
        return [
            DoughnutStatus::make('Statuses')
                ->count('checkins.status')
                ->labels(self::STATUSES)
                ->colors(self::STATUS_BADGES),
            DoughnutStatus::make('Programs')
                ->count(groupBy: 'member.program_id', orderBy: 'value', orderDesc: true)
                ->labels(Program::getSelectable()->toArray())
                ->colors(Program::pluck('member_portal_main_color', 'id')->toArray())
                ->hasAccess(Auth::user()->hasTeam('adv_management')),
            ProcessStatus::make(self::slotsTitle())
                ->currentTitle('Available Slots')
                ->currentValue(fn () => $club->available_adult_slots)
                ->targetTitle('Slots')
                ->targetValue(fn () => $club->getAdultSlots())
                ->hint(static::accessTypeDescription())
                ->color(function () use ($club) {
                    $traffic = $club->traffic;

                    return $traffic == 'amber' ? 'orange' : $traffic;
                })
                ->hasAccess($this->isClubAdmin() && $club->partner->display_slots_block),
            ProcessStatus::make(self::slotsTitle(true))
                ->currentTitle('Available Slots')
                ->currentValue(fn () => $club->available_kid_slots)
                ->targetTitle('Slots')
                ->targetValue(fn () => $club->getKidSlots())
                ->hint(static::accessTypeDescription())
                ->color(function () use ($club) {
                    $traffic = $club->traffic;

                    return $traffic == 'amber' ? 'orange' : $traffic;
                })
                ->hasAccess($this->isClubAdmin() && $club->partner->display_slots_block),
        ];
    }

    protected function memberUrlCallback()
    {
        return function ($record) {
            if (Auth::user()->hasTeam('adv_management')) {
                return $record->member?->admin_url;
            }
        };
    }

    protected function isClubAdmin(): bool
    {
        return Auth::user()->hasTeam('club_admins');
    }

    public static function label(): string
    {
        $club = Auth::user()->club;

        return $club ? "Check-ins ({$club->title})" : 'Check-ins';
    }

    public static function singularLabel(): string
    {
        return 'Check-in';
    }

    public static function accessTypeDescription(): string
    {
        if (!Auth::user()->club) {
            return '';
        }
        if (Auth::user()->club->access_type == Club::ACCESS_TYPES['slots']) {
            return 'Slots: each purchased membership creates adult/ children slots. During the check-in, the club employee must correctly deduct the number of slots as per the incoming party. Each slot gives access to Club to use facilities as per the details below with a valid card of ADVANTAGE PLUS. On the occasion that all slots are being occupied or there are not enough free adult slots to accommodate the size of the incoming party, the Advantage PLUS members are offered to wait until someone checks-out or to pay a guest rate to enter the club.';
        }

        return 'Revolving slots: each slot can be used on a revolving basis. If a member/child checks-in to the club, the slot is counted as occupied. Once the member/child checks-out the slots becomes available for the next member to check-in. On the occasion that all slots are being occupied or there are not enough free adult slots to accommodate the size of the incoming party, the Advantage PLUS members are offered to wait until someone checks-out or to pay a guest rate to enter the club.';
    }

    public static function slotsTitle($forKids = false): string
    {
        if (!Auth::user()->club) {
            return '';
        }
        if (Auth::user()->club->access_type == Club::ACCESS_TYPES['slots']) {
            return ($forKids ? 'Kids ' : '').'Slots (Slots)';
        }

        return ($forKids ? 'Kids ' : '').'Slots (Revolving)';
    }
}
