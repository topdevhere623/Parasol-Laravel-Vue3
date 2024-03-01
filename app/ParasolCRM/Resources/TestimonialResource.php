<?php

namespace App\ParasolCRM\Resources;

use App\Models\Program;
use App\Models\Testimonial;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Media;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\DateFilterField;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\SelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class TestimonialResource extends ResourceScheme
{
    public static $model = Testimonial::class;

    protected const STATUS_BADGES = [
        'active' => 'green',
        'inactive' => 'red',
    ];

    public function fields(): array
    {
        return [
            HorizontalRadioButton::make('status')
                ->options(Testimonial::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            Text::make('name')
                ->rules('required'),
            Text::make('city')
                ->rules('required'),
            Text::make('review')
                ->rules('required')
                ->onlyOnForm(),
            Media::make('photo')
                ->onlyOnForm(),
            Date::make('created_at', 'Created time')
                ->onlyOnTable()
                ->sortable(),
            BelongsTo::make('program', Program::class)
                ->titleField('name')
                ->rules('required'),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'name')
                ->quick(),

            EqualFilter::make(
                SelectFilterField::make()
                    ->options(Testimonial::getConstOptions('statuses')),
                'status',
                'status'
            )
                ->quick(),

            EqualFilter::make(DateFilterField::make(), 'created_at')
                ->quick(),

            LikeFilter::make(TextFilterField::make(), 'city'),

            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(
                        Program::orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray()
                    ),
                'program',
                'program_id'
            )->quick(),
        ];
    }
}
