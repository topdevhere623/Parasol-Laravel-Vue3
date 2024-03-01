<?php

namespace App\ParasolCRM\Resources;

use App\Models\City;
use App\Models\Club\Club;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\Boolean;
use ParasolCRM\Fields\Number;
use ParasolCRM\Fields\Select;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;
use ParasolCRM\Statuses\DoughnutStatus;

class ClubsCheckinResource extends ResourceScheme
{
    public const TRAFFIC_BADGES = [
        'green' => 'green',
        'red' => 'red',
        'amber' => 'yellow',
    ];

    public static $model = Club::class;

    public static function label()
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
