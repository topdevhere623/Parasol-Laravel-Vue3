<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Member\MembershipDuration;
use ParasolCRMV2\Fields\Color;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\ResourceScheme;

class MembershipDurationResource extends ResourceScheme
{
    public static $model = MembershipDuration::class;

    public function fields(): array
    {
        return [
            Text::make('title')
                ->rules('required')
                ->sortable(),
            Color::make('color')
                ->rules('required'),
        ];
    }

    public function filters(): array
    {
        return [
            EqualFilter::make(TextFilterField::make(), 'title', null, 'Title')
                ->quick(),
        ];
    }
}
