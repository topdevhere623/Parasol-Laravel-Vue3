<?php

namespace App\ParasolCRM\Resources;

use App\Models\Member\MembershipType;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\ResourceScheme;

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
