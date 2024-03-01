<?php

namespace App\ParasolCRM\Resources;

use App\Models\City;
use App\Models\Club\Club;
use App\Models\Member\Member;
use App\Models\Package;
use App\Models\Plan;
use App\Models\Program;
use App\Models\Reports\ReportClubsByUsage;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Fields\Select;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\BetweenFilter;
use ParasolCRM\Filters\Fields\DateFilterField;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\ResourceScheme;
use ParasolCRM\Statuses\DoughnutStatus;

class ReportClubsByUsageResource extends ResourceScheme
{
    public static $model = ReportClubsByUsage::class;
    public static $defaultSortBy = 'visits_number';
    public static $defaultSortDirection = 'desc';

    public function tableQuery(Builder $query)
    {
        $query
            ->select(DB::raw('COUNT(checkins.id) AS visits_number'))
            ->leftJoin('checkins', 'clubs.id', '=', 'checkins.club_id')
            ->leftJoin('members', 'members.id', '=', 'checkins.member_id')
            ->whereRaw(DB::raw('checkins.deleted_at IS NULL'))
            ->whereRaw(DB::raw('members.deleted_at IS NULL'));
    }

    public function fields(): array
    {
        return [
            Text::make('title')
                ->url(fn ($record) => "/clubs/{$record->id}")
                ->sortable(),

            Select::make('city_id', 'City')
                ->options(
                    City::active()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()
                ),

            Text::make('visits_number')
                ->sortable('visits_number'),
        ];
    }

    public function filters(): array
    {
        return [
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
                new DateFilterField((new Carbon())->modify('-6 months')),
                new DateFilterField(now()),
                'checked_in_at',
                'checkins.checked_in_at',
                'Checkin Date'
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

    public function statuses(): array
    {
        return [
            DoughnutStatus::make('Number of visits by program')
                ->count('members.program_id', 'checkins.id')
                ->colors($this->getPropsList('member_portal_main_color', 'gray'))
                ->labels($this->getPropsList('name', 'Unknown'))
                ->query(function (Builder $builder) {
                    return $builder
                        ->leftJoin('checkins', 'clubs.id', '=', 'checkins.club_id')
                        ->leftJoin('members', 'members.id', '=', 'checkins.member_id')
                        ->whereRaw(DB::raw('checkins.deleted_at IS NULL'))
                        ->whereRaw(DB::raw('members.deleted_at IS NULL'))
                        ->orderByDesc('value');
                }),
        ];
    }

    private function getPropsList($propName, $nullVal = null): array
    {
        $array = Program::pluck($propName, 'id')
            ->toArray();

        return array_map(fn ($val) => $val ?: $nullVal, $array);
    }
}
