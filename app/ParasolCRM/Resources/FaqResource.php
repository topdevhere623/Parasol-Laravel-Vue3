<?php

namespace App\ParasolCRM\Resources;

use App\Models\WebSite\Faq;
use App\Models\WebSite\FaqCategory;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\Editor;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Select;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

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
