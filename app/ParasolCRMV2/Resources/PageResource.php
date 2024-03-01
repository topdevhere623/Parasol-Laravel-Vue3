<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\WebSite\Page;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Containers\HorizontalTab;
use ParasolCRMV2\Containers\TabElement;
use ParasolCRMV2\Fields\Editor;
use ParasolCRMV2\Fields\HorizontalRadioButton;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Fields\Textarea;
use ParasolCRMV2\ResourceScheme;

class PageResource extends ResourceScheme
{
    public static $model = Page::class;

    protected const STATUS_BADGES = [
        'active' => 'green',
        'inactive' => 'red',
    ];

    public function fields(): array
    {
        return [
            HorizontalRadioButton::make('status')
                ->options(Page::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            Text::make('title')
                ->rules('required')
                ->sortable(),
            Text::make('slug')
                ->placeholder('Keep empty to autogenerate from a Title')
                ->sortable(),
            Editor::make('description')
                ->rules('required')
                ->onlyOnForm(),

            Text::make('page_title')
                ->onlyOnForm(),
            Textarea::make('meta_description')
                ->onlyOnForm(),
        ];
    }

    public function layout(): array
    {
        return [
            HorizontalTab::make('')->attach([
                TabElement::make('Basic Information')->attach([
                    Group::make('')->attach([
                        'status',
                        'title',
                        'slug',
                        'description',
                    ]),
                ]),
                TabElement::make('SEO')->attach([
                    Group::make('')->attach([
                        'page_title',
                        'meta_description',
                    ]),
                ]),

            ]),
        ];
    }
}
