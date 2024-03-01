<?php

namespace App\ParasolCRM\Resources;

use App\Models\WebSite\Page;
use ParasolCRM\Containers\Group;
use ParasolCRM\Containers\HorizontalTab;
use ParasolCRM\Containers\TabElement;
use ParasolCRM\Fields\Editor;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Text;
use ParasolCRM\Fields\Textarea;
use ParasolCRM\ResourceScheme;

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
