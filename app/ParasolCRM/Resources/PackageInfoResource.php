<?php

namespace App\ParasolCRM\Resources;

use App\Models\Package;
use App\Models\Program;
use App\Models\WebSite\PackageInfo;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\Boolean;
use ParasolCRM\Fields\Editor;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Media;
use ParasolCRM\Fields\Number;
use ParasolCRM\Fields\Text;
use ParasolCRM\ResourceScheme;

class PackageInfoResource extends ResourceScheme
{
    public static $model = PackageInfo::class;

    protected const STATUS_BADGES = [
        'active' => 'green',
        'inactive' => 'red',
    ];

    protected const TYPE_BADGES = [
        'link' => 'green',
        'package' => 'blue',
        'corporate_offer' => 'yellow',
    ];

    public function fields(): array
    {
        return [
            Text::make('title')
                ->rules('required')
                ->sortable(),

            Text::make('subtitle')
                ->rules('required')
                ->sortable(),

            Media::make('image')
                ->rules('required'),

            Editor::make('description')
                ->rules('required')
                ->onlyOnForm(),

            Number::make('sort')
                ->rules('required')
                ->onlyOnForm(),

            HorizontalRadioButton::make('status')
                ->options(PackageInfo::getConstOptions('statuses'))
                ->default(PackageInfo::STATUSES['inactive'])
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),

            Boolean::make('is_popular')
                ->default(false),

            HorizontalRadioButton::make('type')
                ->options(PackageInfo::getConstOptions('types'))
                ->badges(self::TYPE_BADGES)
                ->rules('required')
                ->sortable(),

            BelongsTo::make('program', Program::class)
                ->titleField('name'),

            Text::make('url')
                ->dependsOn('type', PackageInfo::TYPES['link']),

            BelongsTo::make('package', Package::class)
                ->dependsOn('type', PackageInfo::TYPES['package']),
        ];
    }
}
