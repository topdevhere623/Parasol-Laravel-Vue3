<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Program;
use App\Models\WebSite\GeneralRule;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Fields\BelongsToMany;
use ParasolCRMV2\Fields\HorizontalRadioButton;
use ParasolCRMV2\Fields\Media;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\ResourceScheme;

class GeneralRuleResource extends ResourceScheme
{
    public static $model = GeneralRule::class;

    protected const STATUS_BADGES = [
        'active' => 'green',
        'inactive' => 'red',
    ];

    public function fields(): array
    {
        return [
            HorizontalRadioButton::make('status')
                ->options(GeneralRule::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            Text::make('name')
                ->rules('required')
                ->sortable(),
            Media::make('image'),
            BelongsToMany::make('programs', Program::class, 'programs')
                ->titleField('name'),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('')->attach([
                'status',
                'name',
                'image',
                'programs',
            ]),
        ];
    }
}
