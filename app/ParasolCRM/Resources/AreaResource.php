<?php

namespace App\ParasolCRM\Resources;

use App\Models\Area;
use App\Models\City;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

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
