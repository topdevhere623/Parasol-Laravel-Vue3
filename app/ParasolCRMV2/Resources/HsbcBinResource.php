<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\HSBCBin;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Fields\Boolean;
use ParasolCRMV2\Fields\HorizontalRadioButton;
use ParasolCRMV2\Fields\Number;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\SelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

class HsbcBinResource extends ResourceScheme
{
    public static $model = HSBCBin::class;

    protected const STATUS_BADGES = [
        'inactive' => 'gray',
        'active' => 'green',
    ];

    protected const TYPE_BADGES = [
        'test' => 'red',
        'credit' => 'gray',
        'debit' => 'green',
    ];

    public function fields(): array
    {
        return [
            HorizontalRadioButton::make('status')
                ->options(HSBCBin::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            HorizontalRadioButton::make('type')
                ->options(HSBCBin::getConstOptions('types'))
                ->badges(self::TYPE_BADGES)
                ->rules('required')
                ->sortable(),
            Text::make('title')
                ->rules('required')
                ->sortable(),
            Number::make('bin')
                ->rules('required')
                ->sortable(),
            Boolean::make('free_checkout')
                ->sortable(),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('')->attach([
                'status',
                'type',
                'title',
                'bin',
                'free_checkout',
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            EqualFilter::make(
                SelectFilterField::make()
                    ->options(HSBCBin::getConstOptions('statuses')),
                'status'
            )
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(HSBCBin::getConstOptions('types')),
                'type'
            )
                ->quick(),
            EqualFilter::make(TextFilterField::make(), 'title')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'bin')
                ->quick(),
        ];
    }

    public function layoutFilters(): array
    {
        return [
            Group::make('')->attach([
                'status',
                'type',
                'title',
                'bin',
            ]),
        ];
    }

    public static function label(): string
    {
        return 'HSBC Bins';
    }

    public static function singularLabel(): string
    {
        return 'HSBC Bin';
    }
}
