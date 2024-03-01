<?php

namespace App\ParasolCRM\Resources;

use App\Models\Member\MembershipSource;
use ParasolCRM\Fields\Boolean;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\SelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class MembershipSourceResource extends ResourceScheme
{
    public static $model = MembershipSource::class;

    protected const DISPLAY_ON_BOOKING = [
        'No', 'Yes',
    ];

    public function fields(): array
    {
        return [
            Text::make('title')
                ->sortable(),
            Boolean::make('display_on_booking'),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'title', null, 'Title')
                ->quick(),
            EqualFilter::make(
                SelectFilterField::make()
                    ->options(self::DISPLAY_ON_BOOKING),
                'display_on_booking'
            )
                ->quick(),
        ];
    }
}
