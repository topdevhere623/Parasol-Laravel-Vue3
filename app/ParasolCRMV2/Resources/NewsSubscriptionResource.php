<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\NewsSubscription;
use ParasolCRMV2\Fields\DateTime;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\BetweenFilter;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

class NewsSubscriptionResource extends ResourceScheme
{
    public static $model = NewsSubscription::class;

    public function fields(): array
    {
        return [
            Text::make('email')
                ->rules('required')
                ->sortable(),
            DateTime::make('created_at', 'Subscription Date')
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(
                TextFilterField::make(),
                'email',
            )->quick(),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'created_at',
                null,
                'Subscription Date'
            )
                ->quick(),
        ];
    }
}
