<?php

namespace App\ParasolCRM\Resources;

use App\Models\NewsSubscription;
use ParasolCRM\Fields\DateTime;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\BetweenFilter;
use ParasolCRM\Filters\Fields\DateFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

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
