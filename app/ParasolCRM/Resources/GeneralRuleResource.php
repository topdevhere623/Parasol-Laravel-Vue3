<?php

namespace App\ParasolCRM\Resources;

use App\Models\Program;
use App\Models\WebSite\GeneralRule;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\BelongsToMany;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Media;
use ParasolCRM\Fields\Text;
use ParasolCRM\ResourceScheme;

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
