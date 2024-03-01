<?php

namespace App\ParasolCRM\Resources;

use App\Models\Corporate;
use ParasolCRM\Fields\Boolean;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\SelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

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
