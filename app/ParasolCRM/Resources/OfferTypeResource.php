<?php

namespace App\ParasolCRM\Resources;

use App\Models\OfferType;
use ParasolCRM\Fields\Text;
use ParasolCRM\ResourceScheme;

class OfferTypeResource extends ResourceScheme
{
    public static $model = OfferType::class;

    public function fields(): array
    {
        return [
            Text::make('name')
                ->rules('required')
                ->sortable(),
        ];
    }
}
