<?php

namespace App\ParasolCRM\Resources;

use App\Models\Payments\PaymentMethod;
use App\Models\Zoho\ZohoChartOfAccount;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Select;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\SelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class PaymentMethodResource extends ResourceScheme
{
    public static $model = PaymentMethod::class;

    public const STATUS_BADGES = [
        'inactive' => 'red',
        'active' => 'green',
    ];

    public function fields(): array
    {
        return [
            Text::make('title')
                ->sortable(),
            Text::make('website_title')
                ->sortable(),
            Text::make('code')
                ->unfillableRecord()
                ->sortable(),
            HorizontalRadioButton::make('status')
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
