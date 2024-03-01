<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\City;
use App\Models\Club\Club;
use App\Models\Member\Member;
use App\Models\Package;
use App\Models\Plan;
use App\Models\Program;
use App\Models\Reports\ReportClubsByMemberSelection;
use DB;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\BetweenFilter;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\ResourceScheme;

class ReportClubsByMemberSelectionResource extends ResourceScheme
{
    private const STATUS_BADGES = [
        'active' => 'green',
        'paused' => 'yellow',
        'in_progress' => 'orange',
        'cancelled' => 'red',
        'inactive' => 'gray',
    ];

    public static $model = ReportClubsByMemberSelection::class;
    public static $defaultSortBy = 'members_count';
    public static $defaultSortDirection = 'desc';

    public function tableQuery(Builder $query)
    {
        $query
            ->select(DB::raw('COUNT(members.id) AS members_count'))
            ->leftJoin('member_club', 'clubs.id', '=', 'member_club.club_id')
            ->leftJoin('members', 'members.id', '=', 'member_club.member_id')
            ->join('plans', 'plans.id', '=', 'members.plan_id')
            ->whereRaw(DB::raw('members.deleted_at IS NULL'));
    }

    public function fields(): array
    {
        return [
            Text::make('title')
                ->url(fn ($record) => "/clubs/{$record->id}")
                ->sortable(),

            Text::make('status')
                ->badges(self::STATUS_BADGES)
                ->sortable(),

            Select::make('city_id', 'City')
                ->options(
                    City::active()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()
                ),

            Text::make('members_count')
                ->sortable('members_count'),
        ];
    }

    public function filters(): array
    {
        return [
            // Program
            EqualFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Program::getSelectable()),
                'program',
                'members.program_id'
            )->quick(),
            // Package
            EqualFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Package::getSelectable()),
                'package',
                'members.package_id'
            )->quick(),
            // Plan
            EqualFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Plan::getSelectable()),
                'plan',
                'members.plan_id'
            )->quick(),
            // Allowed plan club type
            EqualFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Plan::getConstOptions('allowed_club_types')),
                'allowed_club_type',
                'plans.allowed_club_type'
            )->quick(),
            // Membership status
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Member::getConstOptions('membership_statuses')),
                'membership_status',
                'members.membership_status'
            ),
            // Start date
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'start_date',
                'members.start_date',
                'Start Date'
            ),
            // Expiry date
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'end_date',
                'members.end_date',
                'Expiry Date'
            ),
            // Club status
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Club::getConstOptions('statuses')),
                'Club status',
                'clubs.status'
            ),
        ];
    }
}
