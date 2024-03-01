<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\OfferType;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\ResourceScheme;

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
