<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\City;
use App\Models\Club\Club;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Fields\Boolean;
use ParasolCRMV2\Fields\Number;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;
use ParasolCRMV2\Statuses\DoughnutStatus;

class ClubsCheckinResource extends ResourceScheme
{
    public const TRAFFIC_BADGES = [
        'green' => 'green',
        'red' => 'red',
        'amber' => 'yellow',
    ];

    public static $model = Club::class;

    public static function label(): string
    {
        return 'Clubs Checked-in';
    }

    public function query(Builder $query)
    {
        $query->selectRaw('created_at as created_at2')->where('clubs.checkin_availability', true)
            ->withCount([
                'checkins' => function (Builder $query) {
                    $query->where('created_at', '>=', today());
                },
                'checkins as checkins_active_count' => function (Builder $query) {
                    $query->active()->where('created_at', '>=', today());
                },
            ]);
    }

    public function fields(): array
    {
        return [
            Text::make('title')
                ->url('/clubs/{id}')
                ->sortable(),
            Select::make('traffic')
                ->options(Club::getConstOptions('traffics'))
                ->badges(self::TRAFFIC_BADGES),
            Number::make('checkins_count', 'Check-ins')
                ->sortable('checkins_count'),
            Number::make('checkins_active_count', 'Active Check-ins')
                ->sortable('checkins_active_count'),
            Number::make('adult_slots')
                ->sortable(),
            Number::make('kid_slots')
                ->sortable(),
            Select::make('access_type')
                ->options(Club::getConstOptions('ACCESS_TYPES'))
                ->sortable(),
            Boolean::make('checkin_over_slots', 'Can checkin over slots')
                ->sortable(),
            Boolean::make('display_slots_block')
                ->sortable(),
            Number::make('auto_checkout_after', 'Check-out after')
                ->displayHandler(fn ($record) => $record->auto_checkout_after.' min')
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'title', null, 'Title')
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Club::getConstOptions('traffics')),
                'traffic',
                'traffic'
            )
                ->quick(),
        ];
    }

    public function statuses(): array
    {
        return [
            DoughnutStatus::make('Cities')
                ->count('clubs.city_id')
                ->labels(City::pluck('name', 'id')->toArray()),
            DoughnutStatus::make('Traffics')
                ->count('clubs.traffic')
                ->labels(Club::getConstOptions('traffic'))
                ->colors(self::TRAFFIC_BADGES),
        ];
    }

    public function layoutFilters(): array
    {
        return [
            Group::make('')->attach([
                'title',
                'traffic',
            ]),
        ];
    }
}
