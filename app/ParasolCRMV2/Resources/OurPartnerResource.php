<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\OurPartner;
use App\Models\Program;
use ParasolCRMV2\Fields\BelongsToMany;
use ParasolCRMV2\Fields\Logo;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\ResourceScheme;

class OurPartnerResource extends ResourceScheme
{
    public static $model = OurPartner::class;

    public function fields(): array
    {
        return [
            Text::make('title')
                ->sortable()
                ->rules('required'),

            Logo::make('logo')
                ->rules('required'),

            Text::make('url'),

            BelongsToMany::make('programs', Program::class)
                ->titleField('name')
                ->rules('required'),
        ];
    }

    public function filters(): array
    {
        return [
            EqualFilter::make(TextFilterField::make(), 'title', null, 'Title')
                ->quick(),

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
