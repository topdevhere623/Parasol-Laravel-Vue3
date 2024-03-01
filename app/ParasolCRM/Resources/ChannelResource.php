<?php

namespace App\ParasolCRM\Resources;

use App\Models\Channel;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\SelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class ChannelResource extends ResourceScheme
{
    public const STATUS_BADGES = [
        'active' => 'green',
        'inactive' => 'red',
        'expired' => 'gray',
    ];

    public static $model = Channel::class;

    public function fields(): array
    {
        return [
            Text::make('title')
                ->rules('required')
                ->sortable(),
            HorizontalRadioButton::make('status')
                ->options(Channel::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->default(Channel::STATUSES['active'])
                ->rules('required')
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'title', null, 'Title')
                ->quick(),

            EqualFilter::make(
                SelectFilterField::make()
                    ->options(Channel::getConstOptions('statuses')),
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
                'title',
                'status',
            ]),
        ];
    }
}
