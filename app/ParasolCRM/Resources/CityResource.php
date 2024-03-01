<?php

namespace App\ParasolCRM\Resources;

use App\Models\City;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\SelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class CityResource extends ResourceScheme
{
    public static $model = City::class;

    public const STATUS_BADGES = [
        'active' => 'green',
        'inactive' => 'red',
    ];

    public function fields(): array
    {
        return [
            BelongsTo::make('country')
                ->rules('required')
                ->sortable(),

            Text::make('name')
                ->rules('required')
                ->sortable(),

            HorizontalRadioButton::make('status')
                ->options(City::getConstOptions('statuses'))
                ->badges(static::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('')->attach([
                'name',
                'status',
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(
                TextFilterField::make(),
                'cities.name',
                'name',
                'Name'
            )->quick(),

            EqualFilter::make(
                SelectFilterField::make()
                    ->options(City::getConstOptions('statuses')),
                'status',
                'status',
            )->quick(),
        ];
    }
}
