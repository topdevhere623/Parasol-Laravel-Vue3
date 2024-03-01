<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Corporate;
use ParasolCRMV2\Fields\Boolean;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\SelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

class CorporateResource extends ResourceScheme
{
    public static $model = Corporate::class;

    protected const BADGES = [
        0 => 'light',
        1 => 'green',
    ];

    public function fields(): array
    {
        return [
            Text::make('title')
                ->sortable(),
            Boolean::make('show_on_main')
                ->badges(self::BADGES)
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'title', null, 'Title')
                ->quick(),
            EqualFilter::make(
                SelectFilterField::make()
                    ->options([
                        0 => 'No',
                        1 => 'Yes',
                    ]),
                'show_on_main'
            )->quick(),
        ];
    }
}
