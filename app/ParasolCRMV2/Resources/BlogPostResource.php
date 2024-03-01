<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\BlogPost\BlogPost;
use ParasolCRMV2\Fields\BelongsToMany;
use ParasolCRMV2\Fields\Boolean;
use ParasolCRMV2\Fields\Date;
use ParasolCRMV2\Fields\Editor;
use ParasolCRMV2\Fields\HorizontalRadioButton;
use ParasolCRMV2\Fields\Media;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Fields\Textarea;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\SelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

class BlogPostResource extends ResourceScheme
{
    public static $model = BlogPost::class;

    public const STATUS_BADGES = [
        'active' => 'green',
        'inactive' => 'light',
    ];

    public function fields(): array
    {
        return [
            Text::make('title')
                ->rules('required')
                ->sortable(),

            Media::make('cover_image')
                ->tooltip('Recommended ratio 5:2')
                ->rules('required')
                ->onlyOnForm(),

            Media::make('preview_image')
                ->tooltip('Recommended ratio 1:1')
                ->rules('required')
                ->onlyOnForm(),

            Editor::make('text')
                ->rules('required')
                ->onlyOnForm(),

            Text::make('blogger_link')
                ->onlyOnForm(),

            Boolean::make('featured')
                ->default(false),

            HorizontalRadioButton::make('status')
                ->options(BlogPost::getConstOptions('statuses'))
                ->default(BlogPost::STATUSES['active'])
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),

            Boolean::make('blogger_show')
                ->onlyOnForm(),

            Text::make('blogger_name')
                ->dependsOn('blogger_show', true),

            Media::make('blogger_photo')
                ->dependsOn('blogger_show', true)
                ->onlyOnForm(),

            Text::make('slug')->onlyOnForm(),

            Text::make('meta_title'),

            Textarea::make('meta_description'),

            Date::make('date')
                ->default(today())
                ->sortable(),

            BelongsToMany::make('relatedBlogs', BlogPost::class, 'relatedBlogs'),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(
                TextFilterField::make(),
                'title',
            )->quick(),
            LikeFilter::make(
                TextFilterField::make(),
                'slug',
            )->quick(),
            EqualFilter::make(
                SelectFilterField::make()
                    ->options(BlogPost::getConstOptions('statuses')),
                'status',
                'status'
            )
                ->quick(),
        ];
    }
}
