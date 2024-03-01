<?php

namespace App\ParasolCRM\Resources;

use App\Models\Payments\PaymentType;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

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
