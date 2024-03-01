<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Reports\HSBCClubVisitsSummary;
use App\Models\Reports\ReportHSBCMonthlyActiveMember;
use App\Scopes\HSBCComplimentaryPlanScope;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\BetweenFilter;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\ResourceScheme;

class HsbcClubVisitSummaryResource extends ResourceScheme
{
    public static string $model = HSBCClubVisitsSummary::class;

    public function tableQuery(Builder $query)
    {
        $activeUsersQuery = ReportHSBCMonthlyActiveMember::whereRaw(
            'month_year = DATE_FORMAT(checkins.checked_in_at, "%c%Y")'
        )
            ->selectRaw('COUNT(*)')->toSql();

        $query->selectRaw(
            'COUNT(DISTINCT checkins.member_id) as checked_in_unique_members,
            DATE_FORMAT(checked_in_at, "%M %Y") as month,
            800 as purchased_slots'
        )
            ->selectRaw("({$activeUsersQuery}) as unique_members")
            ->join('members', 'members.id', '=', 'checkins.member_id')
            ->groupBy('month')
            ->latest('checked_in_at');

        (new HSBCComplimentaryPlanScope())->apply($query, $query->getModel());
    }

    public function fields(): array
    {
        return [
            Text::make('month'),
            Text::make('purchased_slots', 'Total Memberships Purchased'),
            Text::make('unique_members', 'Total Active Memberships')
                ->column('unique_members'),
            Text::make('active_unique_users', 'Total Individual Users')
                ->column('checked_in_unique_members'),
            //            Text::make('remaining_slots', 'Remaining Unused Slots')
            //                ->computed()
            //                ->displayHandler(fn ($record) => $record->purchased_slots - $record->checked_in_unique_members),
        ];
    }

    public function filters(): array
    {
        return [
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'checked_in_at',
                null,
                'Check-in Date'
            )
                ->quick(),
        ];
    }

    public static function label(): string
    {
        $isAdmin = auth()->user()->isAdmin();
        return $isAdmin ? 'Reports | HSBC Club Visits Summary' : 'Club Visits Summary';
    }
}
