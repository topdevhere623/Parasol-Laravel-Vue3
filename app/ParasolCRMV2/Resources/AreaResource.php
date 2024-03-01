<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Area;
use App\Models\City;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Fields\BelongsTo;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

class AreaResource extends ResourceScheme
{
    public static $model = Area::class;

    public function fields(): array
    {
        return [
            Text::make('name')
                ->rules('required')
                ->sortable(),

            BelongsTo::make('city', City::class)
                ->titleField('name')
                ->url('/cities/{city_id}')
                ->rules('required'),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('')->attach([
                'name',
                'city',
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(
                TextFilterField::make(),
                'name',
                'area.name',
                'Name'
            )->quick(),

            EqualFilter::make(
                MultipleSelectFilterField::make()
                    ->options(City::orderBy('name')->pluck('name', 'id')->toArray()),
                'city',
                'city_id'
            )->quick(),
        ];
    }
}
