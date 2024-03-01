<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Member\MembershipType;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\ResourceScheme;

class MembershipTypeResource extends ResourceScheme
{
    public static $model = MembershipType::class;

    public function fields(): array
    {
        return [
            Text::make('title')
                ->sortable(),
            Text::make('card_title')
                ->sortable(),
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
