<?php

namespace App\ParasolCRM\Resources;

use App\Models\BackofficeUser;
use App\Models\Member\Member;
use App\Models\Referral;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\Avatar;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\Email;
use ParasolCRM\Fields\HasOne;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Phone;
use ParasolCRM\Fields\Select;
use ParasolCRM\Fields\Text;
use ParasolCRM\Fields\Textarea;
use ParasolCRM\Filters\BetweenFilter;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\CascadeSelectFilterField;
use ParasolCRM\Filters\Fields\DateFilterField;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class ReferralResource extends ResourceScheme
{
    public static $model = Referral::class;

    protected const STATUS_BADGES = [
        'active' => 'green',
        'declined' => 'red',
        'joined' => 'gray',
        'contacted' => 'orange',
        'lead' => 'blue',
        'not_responding' => 'red',
    ];

    protected const REWARD_STATUS_BADGES = [
        'pending' => 'orange',
        'complete' => 'green',
        'not_selected' => 'light',
    ];

    public function tableQuery(Builder $query)
    {
        // TODO: refactor this sh*t
        $query->leftJoin('members', 'referrals.used_member_id', 'members.id')
            ->leftJoin('backoffice_users', 'members.bdm_backoffice_user_id', 'backoffice_users.id')
            ->with('usedMember.bdmBackofficeUser');
    }

    public function fields(): array
    {
        return [
            HasOne::make('member', Member::class, 'member')
                ->fields([
                    Text::make('full_name', 'Refer Full Name')
                        ->column('first_name')
                        ->sortable()
                        ->url($this->memberUrlCallback())
                        ->displayHandler(fn ($record) => optional($record->member)->fullName),
                    Avatar::make('avatar')
                        ->displayHandler(fn ($record) => file_url($record->member, 'avatar', 'small'))
                        ->username('member_first_name'),
                ])
                ->onlyOnTable(),

            Text::make('name', 'Full Name')
                ->rules('required'),
            Email::make('email'),
            Date::make('created_at', 'Submitted')
                ->sortable()
                ->onlyOnTable(),
            Phone::make('mobile'),
            HorizontalRadioButton::make('status')
                ->options(Referral::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            Text::make('code')
                ->url(fn ($record) => '/coupons/'.optional($record->coupon)->id)
                ->rules('required')
                ->sortable(),
            Text::make('member_no', 'Membership NO')
                ->url($this->usedMemberUrlCallback()),

            Select::make('member_id', 'Referrer Member')
                ->rules('required')
                ->endpoint('/referral/getMemberShotData')
                ->autocomplete()
                ->onlyOnForm(),

            Select::make('used_member_id', 'For member')
                ->rules('required')
                ->endpoint('/referral/getMemberFullData')
                ->autocomplete()
                ->computed()
                ->onlyOnForm(),

            HasOne::make('usedMember', Member::class, 'usedMember')
                ->fields([
                    Date::make('created_at', 'Joined')
                        ->displayHandler(
                            fn ($record) => optional(optional($record->usedMember)->created_at)->format('d F Y')
                        )
                        ->sortable(),
                ])
                ->onlyOnTable(),
            Text::make('backoffice_user_name', 'Sales Person')
                ->displayHandler(function (Referral $record) {
                    return $record->usedMember?->bdmBackofficeUser?->full_name;
                })
                ->onlyOnTable()
                ->hideOnTable()
                ->sortable('backoffice_users.name'),

            Textarea::make('notes', 'Notes')
                ->onlyOnForm(),

            Select::make('reward', 'Reward')
                ->options(Referral::getConstOptions('rewards')),

            Select::make('reward_status', 'Reward status')
                ->badges(self::REWARD_STATUS_BADGES)
                ->rules('required')
                ->options(Referral::getConstOptions('reward_statuses')),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('')->attach([
                'member_id',
                'status',
                'used_member_id',
                'name',
                'email',
                'mobile',
                'code',
                'member_no',
                'reward',
                'reward_status',
                'notes',
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            EqualFilter::make(
                CascadeSelectFilterField::make()
                    ->endpoint('/referral/getMemberShotData')
                    ->autocomplete(),
                'referrals.member_id',
                'referrals.member_id',
                'Referrer Member'
            )
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Referral::getConstOptions('statuses')),
                'status',
                'status'
            )
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'code', null, 'Code')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'email', 'referrals.email')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'member.first_name', null, 'Member first name'),
            LikeFilter::make(TextFilterField::make(), 'member.last_name', null, 'Member last name'),
            LikeFilter::make(TextFilterField::make(), 'mobile'),
            LikeFilter::make(TextFilterField::make(), 'member_no'),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'submitted_at',
                'referrals.created_at',
                'Submitted'
            ),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'joined_at',
                'usedMember.created_at',
                'Joined'
            ),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(BackofficeUser::getSelectable()),
                'sales_person',
                'backoffice_users.id',
            ),
        ];
    }

    protected function memberUrlCallback(): \Closure
    {
        return function ($record) {
            if ($member = $record->member) {
                if ($member->member_type == 'member') {
                    return "/member-primary/{$member->id}";
                }
                if ($member->member_type == 'partner') {
                    return "/member-primary/{$member->parent_id}/member-partner/{$member->id}";
                }
                if ($member->member_type == 'junior') {
                    return "/member-primary/{$member->parent_id}/junior/{$member->id}";
                }
            }
        };
    }

    protected function usedMemberUrlCallback(): \Closure
    {
        return function ($record) {
            if ($member = $record->usedMember) {
                if ($member->member_type == 'member') {
                    return "/member-primary/{$member->id}";
                }
                if ($member->member_type == 'partner') {
                    return "/member-primary/{$member->parent_id}/member-partner/{$member->id}";
                }
                if ($member->member_type == 'junior') {
                    return "/member-primary/{$member->parent_id}/junior/{$member->id}";
                }
            }
        };
    }
}
