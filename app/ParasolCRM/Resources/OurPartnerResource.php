<?php

namespace App\ParasolCRM\Resources;

use App\Models\OurPartner;
use App\Models\Program;
use ParasolCRM\Fields\BelongsToMany;
use ParasolCRM\Fields\Logo;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\ResourceScheme;

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
