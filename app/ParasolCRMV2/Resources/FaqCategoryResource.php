<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\WebSite\FaqCategory;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Fields\Date;
use ParasolCRMV2\Fields\HorizontalRadioButton;
use ParasolCRMV2\Fields\Number;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\ResourceScheme;

class FaqCategoryResource extends ResourceScheme
{
    public static $model = FaqCategory::class;

    protected const STATUS_BADGES = [
        'active' => 'green',
        'inactive' => 'red',
    ];

    public function fields(): array
    {
        return [
            HorizontalRadioButton::make('status')
                ->options(FaqCategory::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            Text::make('name')
                ->rules('required')
                ->sortable(),
            Number::make('sort', 'Sorting')
                ->rules('required')
                ->sortable(),

            Date::make('created_at', 'Created')
                ->unfillableRecord()
                ->onlyOnTable(),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('')->attach([
                'status',
                'name',
                'sort',
            ]),
        ];
    }
}
