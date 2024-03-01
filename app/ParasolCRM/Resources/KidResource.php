<?php

namespace App\ParasolCRM\Resources;

use App\Models\Member\Kid;
use ParasolCRM\Containers\Group;
use ParasolCRM\Containers\TabElement;
use ParasolCRM\Containers\VerticalTab;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\Hidden;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\DateFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class KidResource extends ResourceScheme
{
    public static $model = Kid::class;

    public function fields(): array
    {
        return [
            Hidden::make('parent_id'),
            Text::make('parent_id', 'Primary member')
                ->displayHandler(fn ($record) => optional($record->member)->full_name)
                ->url('/member-primary/{parent_id}')
                ->sortable()
                ->onlyOnTable(),
            Text::make('member_id')
                ->sortable(),
            Text::make('full_name', 'Full name')
                ->computed()
                ->setFromRecordHandler(function ($values) {
                    return $values['first_name'].' '.$values['last_name'] ;
                })
                ->onlyOnTable(),
            Text::make('first_name')
                ->hideOnTable(),
            Text::make('last_name')
                ->hideOnTable(),
            Text::make('age', 'Age')
                ->column('dob')
                ->displayHandler(fn ($record) => $record->age)
                ->sortable()
                ->onlyOnTable(),
            Date::make('dob', 'Date of birth')
                ->hideOnTable(),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('Membership details')->attach([
                'member_id',
                'first_name',
                'last_name',
                'dob',
            ]),
        ];
    }
    public function filters(): array
    {
        return [
            EqualFilter::make(TextFilterField::make(), 'parent_id')
                ->hidden(),

            LikeFilter::make(TextFilterField::make(), 'member_id', null, 'Member id')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'first_name')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'last_name')
                ->quick(),

            EqualFilter::make(DateFilterField::make(), 'dob'),
        ];
    }

    public function layoutFilters(): array
    {
        return [
            VerticalTab::make()->attach([
                TabElement::make('Basic information')->attach([
                    'member_id',
                    'first_name',
                    'last_name',
                    'dob',
                ]),
            ]),
        ];
    }
}
