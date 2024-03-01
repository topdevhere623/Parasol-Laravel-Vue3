<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\City;
use App\Models\Country;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Fields\BelongsTo;
use ParasolCRMV2\Fields\HorizontalRadioButton;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\SelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

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
            BelongsTo::make('country', Country::class)
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
