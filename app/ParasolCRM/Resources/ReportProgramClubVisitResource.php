<?php

namespace App\ParasolCRM\Resources;

use App\Models\Member\Member;
use App\Models\Reports\ProgramReportCheckin;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\Email;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\BetweenFilter;
use ParasolCRM\Filters\Fields\DateFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class ReportProgramClubVisitResource extends ResourceScheme
{
    public static string $model = ProgramReportCheckin::class;

    public function tableQuery(Builder $query)
    {
        $query->selectRaw('CONCAT_WS(" ", first_name, last_name) as full_name, member_type, member.avatar')

            // TODO: refactor member avatar
            ->with('member', 'club');
    }

    public function fields(): array
    {
        return [
            BelongsTo::make('member', Member::class, null, 'Membership #')
                ->sortable()
                ->titleField('member_id'),
            Text::make('first_name')
                ->sortable(),
            Text::make('last_name')
                ->sortable(),
            Email::make('login_email', 'Email')
                ->sortable(),
            Date::make('checked_in_at', 'Check-in Date')
                ->sortable(),

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
        return $isAdmin ? 'Reports | Program Club Visits' : 'Club Visits';
    }
}
