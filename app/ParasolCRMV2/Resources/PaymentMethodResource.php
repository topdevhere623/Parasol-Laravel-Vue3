<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Payments\PaymentMethod;
use App\Models\Zoho\ZohoChartOfAccount;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\SelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

class PaymentMethodResource extends ResourceScheme
{
    public static $model = PaymentMethod::class;

    public const STATUS_BADGES = [
        'inactive' => 'secondary',
        'active' => 'success',
    ];

    public function fields(): array
    {
        return [
            Text::make('title')
                ->rules('required')
                ->sortable(),
            Text::make('website_title')
                ->rules('required')
                ->sortable(),
            Text::make('code')
                ->unfillableRecord()
                ->sortable(),
            Select::make('status')
                ->options(PaymentMethod::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->sortable(),
            Select::make('zoho_chartofaccount_id', 'Zoho Account')
                ->options(ZohoChartOfAccount::getSelectable())
                ->onlyOnForm(),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'title', null, 'Title')
                ->quick(),
            EqualFilter::make(
                SelectFilterField::make()
                    ->options(PaymentMethod::getConstOptions('statuses')),
                'status',
                'status'
            )
                ->quick(),
            EqualFilter::make(TextFilterField::make(), 'code', null, 'Code')
                ->quick(),
        ];
    }
}
