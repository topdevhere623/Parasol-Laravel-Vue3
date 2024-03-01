<?php

namespace App\ParasolCRM\Resources;

use App\Models\Club\ClubTag;
use ParasolCRM\Fields\Color;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

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
