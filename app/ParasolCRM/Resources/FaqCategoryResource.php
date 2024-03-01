<?php

namespace App\ParasolCRM\Resources;

use App\Models\WebSite\FaqCategory;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Number;
use ParasolCRM\Fields\Text;
use ParasolCRM\ResourceScheme;

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
