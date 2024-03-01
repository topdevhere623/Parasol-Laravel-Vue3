<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\WebSite\Faq;
use App\Models\WebSite\FaqCategory;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Fields\Editor;
use ParasolCRMV2\Fields\HorizontalRadioButton;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

class FaqResource extends ResourceScheme
{
    public static $model = Faq::class;

    protected const STATUS_BADGES = [
        'active' => 'green',
        'inactive' => 'red',
    ];

    public function fields(): array
    {
        return [
            HorizontalRadioButton::make('status')
                ->options(Faq::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            Select::make('category_id', 'Category')
                ->options($this->getFaqCategories())
                ->rules('required')
                ->sortable(),
            Text::make('question')
                ->rules('required'),
            Editor::make('answer')
                ->rules('required')
                ->onlyOnForm(),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('')->attach([
                'status',
                'category_id',
                'question',
                'answer',
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options($this->getFaqCategories()),
                'category_id',
                'category_id',
                'Category'
            )
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'question', null, 'Question')
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Faq::getConstOptions('statuses')),
                'status',
                'status'
            )
                ->quick(),
        ];
    }

    public function layoutFilters(): array
    {
        return [
            Group::make('')->attach([
                'category_id',
                'question',
                'status',
            ]),
        ];
    }

    protected function getFaqCategories(): array
    {
        return FaqCategory::active()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
