<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Member\MembershipSource;
use ParasolCRMV2\Fields\Boolean;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\SelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

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
