<?php

namespace App\ParasolCRM\Resources;

use App\Models\Club\Club;
use App\Models\Member\Member;
use App\Models\Member\MembershipType;
use App\Models\Package;
use App\Models\Plan;
use App\Models\Program;
use App\Models\Reports\ReportTopMember;
use App\ParasolCRM\Filters\NonZeroCheckinsCountFilter;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\BetweenFilter;
use ParasolCRM\Filters\Fields\DateFilterField;
use ParasolCRM\Filters\Fields\HorizontalRadioButtonFilterField;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\ResourceScheme;

class ReportTopMemberResource extends ResourceScheme
{
    public static $model = ReportTopMember::class;

    public static $defaultSortBy = 'visits_number';

    public static $defaultSortDirection = 'desc';

    public function tableQuery(Builder $query)
    {
        $query
            ->select(DB::raw('COUNT(DISTINCT checkins.id) AS visits_number'))
            ->leftJoin('checkins', function (QueryBuilder $query) {
                $query->where('checkins.member_id', DB::raw('members.id'))
                    ->whereNull('checkins.deleted_at');
            })
            ->with('program', 'package', 'plan', 'bdmBackofficeUser', 'member.bdmBackofficeUser');
    }

    public function fields(): array
    {
        return [
            Text::make('member_id', 'Membership Number')
                ->url($this->memberUrlCallback())
                ->sortable(),

            BelongsTo::make('membershipType', MembershipType::class)
                ->sortable('membershipType.title'),

            Text::make('first_name')
                ->sortable(),

            Text::make('last_name')
                ->sortable(),

            Text::make('program_id', 'Program')
                ->displayHandler(fn ($record) => optional($record->program)->name)
                ->url(fn ($record) => '/programs/'.optional($record->program)->id)
                ->sortable(),

            Text::make('package_id', 'Package')
                ->displayHandler(fn ($record) => optional($record->package)->title)
                ->url(fn ($record) => '/packages/'.optional($record->package)->id)
                ->sortable(),

            Text::make('plan_id', 'Plan')
                ->displayHandler(fn ($record) => optional($record->plan)->title)
                ->url(fn ($record) => '/plans/'.optional($record->plan)->id)
                ->hideOnTable()
                ->sortable(),

            Text::make('bdm_backoffice_user_id', 'BDM')
                ->displayHandler(
                    fn ($record) => $record->bdmBackofficeUser?->full_name
                        ?? $record->member?->bdmBackofficeUser?->full_name
                ),

            Text::make('visits_number')
                ->sortable('visits_number'),

            Date::make('start_date', 'Member start date')
                ->column('start_date')
                ->sortable(),

            Date::make('end_date', 'Member expiry date')
                ->column('end_date')
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            // Non zero visits number
            NonZeroCheckinsCountFilter::make(
                HorizontalRadioButtonFilterField::make(1)
                    ->default(0)
                    ->options([0 => 'No', 1 => 'Yes']),
                'Exclude Zero Visits',
                'visits_number',
            ),
            // Membership type
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(MembershipType::getSelectable()),
                'membershipType',
                'membership_type_id'
            ),
            // Club
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Club::getSelectable()),
                'clubs',
                'checkins.club_id',
                'Clubs'
            )->quick(),
            // Program
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Program::getSelectable()),
                'program',
                'members.program_id'
            )->quick(),
            // Package
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Package::getSelectable()),
                'package',
                'members.package_id'
            )->quick(),
            // Plan
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Plan::getSelectable()),
                'plan',
                'members.plan_id'
            )->quick(),
            // Checkin Date
            BetweenFilter::make(
                new DateFilterField(),
                new DateFilterField(),
                'checked_in_at',
                'checked_in_at',
                'Checkin Date'
            )->quick(),
            // Member start date
            BetweenFilter::make(
                new DateFilterField(),
                new DateFilterField(),
                'start_date',
                'start_date',
                'Member start date'
            ),
            // Member expiry date
            BetweenFilter::make(
                new DateFilterField(),
                new DateFilterField(),
                'end_date',
                'end_date',
                'Member expiry date'
            ),
        ];
    }

    protected function memberUrlCallback()
    {
        return function (Member $member) {
            if (\Auth::user()->hasTeam('adv_management')) {
                return $member->admin_url;
            }
        };
    }
}
