<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Club\Club;
use App\Models\Offer;
use App\Models\OfferType;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Containers\TabElement;
use ParasolCRMV2\Containers\VerticalTab;
use ParasolCRMV2\Fields\BelongsTo;
use ParasolCRMV2\Fields\BelongsToMany;
use ParasolCRMV2\Fields\Date;
use ParasolCRMV2\Fields\Editor;
use ParasolCRMV2\Fields\Gallery;
use ParasolCRMV2\Fields\HorizontalRadioButton;
use ParasolCRMV2\Fields\Logo;
use ParasolCRMV2\Fields\Number;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\BetweenFilter;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

class OfferResource extends ResourceScheme
{
    public static $model = Offer::class;

    protected const STATUS_BADGES = [
        'inactive' => 'gray',
        'active' => 'green',
        'expired' => 'red',
    ];

    public function tableQuery(Builder $query)
    {
        $query->with('clubs');
    }

    public function fields(): array
    {
        return [
            HorizontalRadioButton::make('status')
                ->options(Offer::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->default(Offer::STATUSES['active'])
                ->rules('required')
                ->sortable(),
            Text::make('name')
                ->rules('required')
                ->sortable(),
            Select::make('offer_type_id', 'Offer type')
                ->rules(['required'])
                ->options($this->getOfferTypes())
                ->sortable(),
            Logo::make('logo')
                ->sortable(),
            Text::make('offer_value')
                ->sortable(),
            Text::make('area')
                ->sortable(),
            Date::make('expiry_date')
                ->sortable(),

            Editor::make('about')
                ->onlyOnForm(),
            Editor::make('terms', 'Terms and conditions')
                ->onlyOnForm(),
            Text::make('location')
                ->onlyOnForm(),
            Text::make('emirate')
                ->onlyOnForm(),
            Text::make('website')
                ->onlyOnForm(),
            Text::make('map', 'Map link')
                ->onlyOnForm(),
            Text::make('offer_code')
                ->onlyOnForm(),
            Text::make('online_shop_link')
                ->onlyOnForm(),
            Number::make('sort')
                ->default(0)
                ->onlyOnForm(),
            BelongsToMany::make('clubs', Club::class, 'clubs')
                ->multiple()
                ->onlyOnForm(),
            BelongsTo::make('clubs', Club::class, 'clubs')
                ->displayHandler(function ($record) {
                    $clubs = $record->clubs->pluck('title')->toArray();
                    return implode(', ', $clubs);
                })
                ->onlyOnTable()
                ->sortable()
                ->hideOnTable(),
            Gallery::make('gallery')
                ->onlyOnForm(),
        ];
    }

    public function layout(): array
    {
        return [
            VerticalTab::make('')->attach([
                TabElement::make('Basic details')->attach([
                    'status',
                    'name',
                    'offer_type_id',
                    'offer_value',
                    'offer_code',
                    'location',
                    'area',
                    'emirate',
                    'website',
                    'map',
                    'online_shop_link',
                    'expiry_date',
                    'clubs',
                    'sort',
                ]),
                TabElement::make('Descriptions')->attach([
                    'about',
                    'terms',
                ]),
                TabElement::make('Gallery')->attach([
                    'logo',
                    'gallery',
                ]),
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'name', null, 'Name')
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options($this->getOfferTypes()),
                'offer_type_id',
                'offer_type_id',
                'Offer type'
            )
                ->quick(),
            BetweenFilter::make(new DateFilterField(), new DateFilterField(), 'expiry_date')
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options($this->getClubs()),
                'clubs',
                'club_id',
                'Club'
            ),
            LikeFilter::make(TextFilterField::make(), 'offer_value'),
            LikeFilter::make(TextFilterField::make(), 'location'),
            LikeFilter::make(TextFilterField::make(), 'area'),
            LikeFilter::make(TextFilterField::make(), 'emirate'),
            LikeFilter::make(TextFilterField::make(), 'website'),
        ];
    }

    public function layoutFilters(): array
    {
        return [
            Group::make('')->attach([
                'name',
                'offer_type_id',
                'expiry_date',
                'clubs',
                'offer_value',
                'location',
                'area',
                'emirate',
                'website',
            ]),
        ];
    }

    protected function getOfferTypes(): array
    {
        return OfferType::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function getClubs(): array
    {
        return Club::orderBy('title')
            ->pluck('title', 'id')
            ->toArray();
    }
}
