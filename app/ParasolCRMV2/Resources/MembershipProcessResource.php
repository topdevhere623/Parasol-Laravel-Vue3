<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Member\MemberPrimary;
use App\Models\Member\MembershipProcess;
use App\Models\WebFormRequest;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Fields\BelongsTo;
use ParasolCRMV2\Fields\Date;
use ParasolCRMV2\Fields\File;
use ParasolCRMV2\Fields\HorizontalRadioButton;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Fields\Textarea;
use ParasolCRMV2\Filters\BetweenFilter;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;
use ParasolCRMV2\Statuses\DoughnutStatus;

class MembershipProcessResource extends ResourceScheme
{
    public static $model = MembershipProcess::class;

    protected const STATUS_BADGES = [
        'pending' => 'gray',
        'complete' => 'green',
        'cancelled' => 'red',
        'overdue' => 'red',
    ];

    public function tableQuery(Builder $query)
    {
        if (!has_filter('member_id')) {
            $query->selectRaw('CONCAT_WS(" ", member.first_name, member.last_name) as full_name');
        }
    }

    public function fields(): array
    {
        return [
            HorizontalRadioButton::make('status')
                ->options(MembershipProcess::getConstOptions('statuses'))
                ->default(MembershipProcess::STATUSES['complete'])
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            Text::make('title')
                ->rules('required'),
            BelongsTo::make('member', MemberPrimary::class, null, 'Member ID')
                ->url('/member-primary/{member_id}')
                ->titleField('member_id')
                ->sortable()
                // ->onlyOnTable()
                ->hasAccess(function () {
                    return !has_filter('member_id');
                }),
            Text::make('full_name', 'Member Full Name')
                ->column('member_id')
                ->onlyOnTable()
                ->displayHandler(fn ($record) => $record->full_name)
                ->hasAccess(function () {
                    return !has_filter('member_id');
                }),
            Textarea::make('note')
                ->hideOnTable(),
            Date::make('action_due_date')
                ->default(today()),
            File::make('file'),

            BelongsTo::make('webFormRequest', WebFormRequest::class)
                ->titleField('name'),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('')->attach([
                'member_id',
                'status',
                'member',
                'title',
                'note',
                'action_due_date',
                'file',
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            EqualFilter::make(TextFilterField::make(), 'member_id', 'membership_processes.member_id')
                ->hidden(),

            InFilter::make(
                (new MultipleSelectFilterField())
                    ->options(MembershipProcess::STATUSES),
                'status',
                'membership_processes.status'
            )->quick(),
            LikeFilter::make(new TextFilterField(), 'title', 'membership_processes.title')
                ->quick(),
            LikeFilter::make(new TextFilterField(), 'member', 'member.member_id')
                ->quick(),
            BetweenFilter::make(
                new DateFilterField(),
                new DateFilterField(),
                'action_due_date',
                'membership_processes.action_due_date',
            )->quick(),
        ];
    }

    public function statuses(): array
    {
        return [
            DoughnutStatus::make('Membership Process types')
                ->count('membership_processes.status')
                ->labels(MembershipProcess::getConstOptions('STATUSES'))
                ->colors(self::STATUS_BADGES),
        ];
    }
}
