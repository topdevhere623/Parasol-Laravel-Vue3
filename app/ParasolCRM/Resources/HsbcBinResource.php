<?php

namespace App\ParasolCRM\Resources;

use App\Models\HSBCBin;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\Boolean;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Number;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\SelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

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

    public static function label()
    {
        return 'HSBC Bins';
    }

    public static function singularLabel()
    {
        return 'HSBC Bin';
    }
}
