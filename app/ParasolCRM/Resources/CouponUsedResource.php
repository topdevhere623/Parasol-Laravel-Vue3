<?php

namespace App\ParasolCRM\Resources;

use App\Models\Channel;
use App\Models\Member\Member;
use App\Models\Member\MemberPrimary;
use App\Models\MemberUsedCoupon;
use App\ParasolCRM\Filters\CouponOwnerFilter;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\Avatar;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\Email;
use ParasolCRM\Fields\HasOne;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\BetweenFilter;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\DateFilterField;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class CouponUsedResource extends ResourceScheme
{
    public static $model = MemberUsedCoupon::class;

    public function fields(): array
    {
        return [
            Text::make('code')
                ->sortable()
                ->onlyOnTable(),
            HasOne::make('member', Member::class)
                ->fields([
                    Text::make('full_name')
                        ->column('first_name')
                        ->sortable()
                        ->onlyOnTable()
                        ->displayHandler(fn ($record) => optional($record->member)->fullName),
                    Text::make('member_id')
                        ->url($this->memberUrlCallback())
                        ->displayHandler(fn ($record) => optional($record->member)->member_id)
                        ->sortable(),
                    Avatar::make('avatar')
                        ->displayHandler(fn ($record) => file_url($record->member, 'avatar', 'small'))
                        ->username('member_first_name'),
                    Email::make('email'),
                ])
                ->url('/members/{member_id}')
                ->onlyOnTable(),
            BelongsTo::make('channel', Channel::class)
                ->url('/channels/{channel_id}')
                ->onlyOnTable()
            ,
            Date::make('created_at')
                ->sortable()
                ->onlyOnTable(),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'code', 'coupons.code')
                ->quick(),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'created_at',
                'member_used_coupons.created_at'
            )
                ->quick(),
            EqualFilter::make(TextFilterField::make(), 'member_id', 'member.member_id'),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(MemberPrimary::getSelectable()),
                'member',
                'member_used_coupons.member_id'
            )
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(
                        Channel::select(['id', 'title'])
                            ->orderBy('title')
                            ->get()
                            ->pluck('title', 'id')
                            ->toArray()
                    ),
                'channel',
                'channel_id'
            )
                ->quick(),
            CouponOwnerFilter::make(TextFilterField::make(), 'owner')
                ->quick(),
        ];
    }

    public function layoutFilters(): array
    {
        return [
            Group::make('')->attach([
                'code',
                'created_at',
                'member_id',
                'channel_id',
                'channel',
                'member',
            ]),
        ];
    }

    public static function singularLabel(): string
    {
        return 'Used Coupon';
    }

    public static function label(): string
    {
        return 'Used Coupons';
    }

    protected function memberUrlCallback()
    {
        return function ($record) {
            if (\Auth::user()->hasTeam('adv_management')) {
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
            }
        };
    }
}
