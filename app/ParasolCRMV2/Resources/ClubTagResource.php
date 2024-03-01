<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Club\ClubTag;
use ParasolCRMV2\Fields\Color;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

class ClubTagResource extends ResourceScheme
{
    public static $model = ClubTag::class;

    public function fields(): array
    {
        return [
            Text::make('name')
                ->sortable(),
            Text::make('slug')
                ->sortable(),
            Color::make('color')
                ->rules('required'),
        ];
    }

    public function filters(): array
    {
        return [
            EqualFilter::make(TextFilterField::make(), 'name', null, 'Name')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'slug', null, 'Slug')
                ->quick(),
        ];
    }
}
