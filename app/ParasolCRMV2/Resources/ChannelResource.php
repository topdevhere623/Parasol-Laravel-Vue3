<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Channel;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Fields\HorizontalRadioButton;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\SelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

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
