<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Reports\ReportLeadTag;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Fields\Number;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\BetweenFilter;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

class ReportLeadTagResource extends ResourceScheme
{
    public static $model = ReportLeadTag::class;

    public static $defaultSortBy = 'leads_count';

    public static $defaultSortDirection = 'desc';

    public function query(Builder $query)
    {
        $query->selectRaw('COUNT(DISTINCT lead_lead_tag.lead_id) as leads_count')
            ->leftJoin('lead_lead_tag', 'lead_lead_tag.lead_tag_id', '=', 'lead_tags.id')
            ->leftJoin('leads', 'leads.id', '=', 'lead_lead_tag.lead_id')
            ->where('lead_category_id', 1);
    }

    public function fields(): array
    {
        return [
            Text::make('name')
                ->sortable(),
            Number::make('leads_count')
                ->sortable('leads_count'),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'name', 'lead_tags.name')
                ->quick(),
            BetweenFilter::make(
                DateFilterField::make()->default(today()->startOfMonth()),
                DateFilterField::make(),
                'lead_created_date',
                'leads.created_at'
            )->quick(),
        ];
    }
}
