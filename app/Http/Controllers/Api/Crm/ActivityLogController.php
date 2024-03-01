<?php

namespace App\Http\Controllers\Api\Crm;

use App\Models\Activity;
use ParasolCRM\Fields\DateTime;
use ParasolCRM\Fields\ID;
use ParasolCRM\Fields\Text;
use ParasolCRM\Fields\Textarea;

class ActivityLogController extends CrmBaseController
{
    public string $model = Activity::class;

    public function fields(): array
    {
        return [
            ID::make()
                ->sortable(),
            Text::make('parent_id', 'Parent ID')
                ->sortable(),
            Text::make('name')
                ->sortable(),
            Textarea::make('description')
                ->sortable(),
            Text::make('user_id')
                ->sortable(),
            Text::make('user_type')
                ->sortable(),
            Text::make('entity_id')
                ->sortable(),
            Text::make('entity_type')
                ->sortable(),
            Text::make('data')
                ->sortable()
                ->onlyOnForm(),
            DateTime::make('created_at')
                ->sortable(),
        ];
    }
}
