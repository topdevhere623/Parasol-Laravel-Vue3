<?php

namespace App\ParasolCRM\Resources;

use App\Models\Member\MembershipDuration;
use ParasolCRM\Fields\Color;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\ResourceScheme;

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
