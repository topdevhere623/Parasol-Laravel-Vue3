<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Program;
use App\Models\Testimonial;
use ParasolCRMV2\Fields\BelongsTo;
use ParasolCRMV2\Fields\Date;
use ParasolCRMV2\Fields\HorizontalRadioButton;
use ParasolCRMV2\Fields\Media;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\SelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

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
