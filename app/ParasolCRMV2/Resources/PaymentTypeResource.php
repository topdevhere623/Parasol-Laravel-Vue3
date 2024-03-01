<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Payments\PaymentType;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

class PaymentTypeResource extends ResourceScheme
{
    public static $model = PaymentType::class;

    public function fields(): array
    {
        return [
            Text::make('title')
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'title', null, 'Title')
                ->quick(),
        ];
    }
}
