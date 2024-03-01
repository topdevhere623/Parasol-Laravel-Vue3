<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\City;
use App\Models\Club\Club;
use App\Models\Club\ClubTag;
use App\Models\Offer;
use App\Models\Partner\Partner;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Containers\TabElement;
use ParasolCRMV2\Containers\VerticalTab;
use ParasolCRMV2\Fields\BelongsTo;
use ParasolCRMV2\Fields\BelongsToMany;
use ParasolCRMV2\Fields\Boolean;
use ParasolCRMV2\Fields\Editor;
use ParasolCRMV2\Fields\Email;
use ParasolCRMV2\Fields\File;
use ParasolCRMV2\Fields\Gallery;
use ParasolCRMV2\Fields\HorizontalRadioButton;
use ParasolCRMV2\Fields\Logo;
use ParasolCRMV2\Fields\Media;
use ParasolCRMV2\Fields\Number;
use ParasolCRMV2\Fields\Phone;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Fields\Textarea;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\SelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;
use ParasolCRMV2\Statuses\DoughnutStatus;

class ClubResource extends ResourceScheme
{
    public const STATUS_BADGES = [
        'active' => 'green',
        'inactive' => 'gray',
        'cancelled' => 'red',
        'paused' => 'gray',
        'in_progress' => 'blue',
    ];

    public const TRAFFIC_BADGES = [
        'green' => 'green',
        'red' => 'red',
        'amber' => 'yellow',
    ];

    public static $model = Club::class;

    public static $defaultSortBy = 'title';

    public static $defaultSortDirection = 'ASC';

    public function tableQuery(Builder $query)
    {
        $query->with('offers');
    }

    public function fields(): array
    {
        return [
            Text::make('title')
                ->rules('required')
                ->sortable(),
            HorizontalRadioButton::make('status')
                ->options(Club::getConstOptions('statuses'))
                ->default(Club::STATUSES['active'])
                ->badges(self::STATUS_BADGES)
                ->rules('required')
                ->sortable(),
            BelongsTo::make('partner', Partner::class)
                ->titleField('name')
                ->sortable('partner.name')
                ->url('/partners/{partner_id}'),
            Boolean::make('is_visible_plan', 'Visible in plan'),
            Boolean::make('is_visible_website', 'Visible in website'),
            Boolean::make('checkin_availability', 'Checkins available'),
            Select::make('city_id', 'City')
                ->options($this->getCities()),
            Logo::make('logo'),
            Boolean::make('is_always_red', 'Traffic always red')
                ->hideOnTable(),
            HorizontalRadioButton::make('traffic')
                ->options(Club::getConstOptions('traffics'))
                ->badges(self::TRAFFIC_BADGES)
                ->default(Club::TRAFFICS['green'])
                ->rules('required')
                ->sortable(),
            Text::make('mc_display_name', 'Membership card display name')
                ->onlyOnForm(),
            Text::make('slug')
                ->placeholder('Keep empty to autogenerate from a Title')
                ->onlyOnForm(),
            Number::make('adult_slots')
                ->onlyOnForm(),
            Number::make('kid_slots')
                ->onlyOnForm(),
            Select::make('access_type')
                ->options(Club::getConstOptions('ACCESS_TYPES'))
                ->onlyOnForm(),
            Boolean::make('checkin_over_slots', 'Can checkin over slots')
                ->onlyOnForm(),
            Boolean::make('display_slots_block')
                ->onlyOnForm(),
            Number::make('auto_checkout_after', 'Auto-check-out after (minutes)')
                ->onlyOnForm(),
            BelongsToMany::make('tags', ClubTag::class, 'tags')
                ->titleField('name')
                ->multiple()
                ->onlyOnForm(),
            Email::make('email')
                ->onlyOnForm(),
            Phone::make('phone')
                ->onlyOnForm(),
            Text::make('address')
                ->rules([
                    'required',
                ])
                ->onlyOnForm(),
            Text::make('website')
                ->onlyOnForm(),
            Phone::make('contact', 'Club`s contact')
                ->onlyOnForm(),
            Text::make('gmap_link', 'Google map link')
                ->rules([
                    'required',
                ])
                ->onlyOnForm(),
            Editor::make('club_overview')
                ->onlyOnForm(),
            Editor::make('what_members_love')
                ->rules([
                    'required',
                ])
                ->onlyOnForm(),
            Editor::make('description')
                ->onlyOnForm(),
            Editor::make('important_updates')
                ->onlyOnForm(),
            Editor::make('guest_fees')
                ->onlyOnForm(),
            Editor::make('opening_hours_notes')
                ->onlyOnForm(),
            Editor::make('check_in_area')
                ->rules('required')
                ->onlyOnForm(),
            Editor::make('booking_policy_for_activities')
                ->rules('required')
                ->onlyOnForm(),
            Editor::make('parking')
                ->rules('required')
                ->onlyOnForm(),
            Media::make('home_photo', 'Home page (size: 800x456)')
                ->onlyOnForm(),
            Media::make('club_photo', 'Clubs page (size: 800x456)')
                ->onlyOnForm(),
            Media::make('checkout_photo', 'Checkout page (size: 600x400)')
                ->onlyOnForm(),
            File::make('detailed_club_info', 'Detailed club info (PDF)')
                ->onlyOnForm(),
            Gallery::make('gallery')
                ->onlyOnForm(),
            Text::make('youtube', 'YOUTUBE (AUTOMATICALLY GENERATE EMBED LINK)')
                ->onlyOnForm(),
            BelongsToMany::make('offers', Offer::class, 'offers')
                ->titleField('name')
                ->multiple()
                ->onlyOnForm(),
            Text::make('Offers')
                ->displayHandler(function ($record) {
                    $offers = $record->offers->pluck('name')->toArray();
                    return implode(', ', $offers);
                })
                ->onlyOnTable()
                ->hideOnTable(),
            Text::make('meta_title')
                ->onlyOnForm(),
            Textarea::make('meta_description')
                ->onlyOnForm(),
        ];
    }

    public function layout(): array
    {
        return [
            VerticalTab::make('')->attach([
                TabElement::make('Basic details')->attach([
                    'status',
                    'partner',
                    'is_visible_plan',
                    'is_visible_website',
                    'checkin_availability',
                    'title',
                    'mc_display_name',
                    'slug',
                ]),
                TabElement::make('Checkins system')->attach([
                    'traffic',
                    'is_always_red',
                    'adult_slots',
                    'kid_slots',
                    'access_type',
                    'checkin_over_slots',
                    'display_slots_block',
                    'auto_checkout_after',
                ]),
                TabElement::make('Additional information')->attach([
                    'city_id',
                    'tags',
                    'email',
                    'phone',
                    'address',
                    'website',
                    'contact',
                    'gmap_link',
                ]),
                TabElement::make('Description')->attach([
                    'club_overview',
                    'what_members_love',
                    'description',
                    'important_updates',
                    'guest_fees',
                    'opening_hours_notes',
                    'check_in_area',
                    'booking_policy_for_activities',
                    'parking',
                ]),
                TabElement::make('Gallery and files')->attach([
                    'home_photo',
                    'club_photo',
                    'checkout_photo',
                    'logo',
                    'detailed_club_info',
                    'gallery',
                ]),
                TabElement::make('YouTube video')->attach([
                    'youtube',
                ]),
                TabElement::make('Linked offers')->attach([
                    'offers',
                ]),
                TabElement::make('SEO')->attach([
                    'meta_title',
                    'meta_description',
                ]),
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'title', null, 'Title')
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Club::getConstOptions('statuses')),
                'status',
                'clubs.status'
            )
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Partner::getSelectable()),
                'partner',
                'clubs.partner_id'
            ),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Club::getConstOptions('traffics')),
                'traffic',
                'clubs.traffic'
            )
                ->quick(),
            EqualFilter::make(
                SelectFilterField::make()
                    ->options([
                        0 => 'No',
                        1 => 'Yes',
                    ]),
                'checkin_availability',
                'clubs.checkin_availability',
                'Checkins available'
            )->quick(),
            EqualFilter::make(
                SelectFilterField::make()
                    ->options([
                        0 => 'No',
                        1 => 'Yes',
                    ]),
                'is_visible_plan',
                'clubs.is_visible_plan',
                'Visibility in Plan'
            ),
            EqualFilter::make(
                SelectFilterField::make()
                    ->options([
                        0 => 'No',
                        1 => 'Yes',
                    ]),
                'is_visible_website',
                'clubs.is_visible_website',
                'Visibility in Website'
            ),
        ];
    }

    public function statuses(): array
    {
        return [
            DoughnutStatus::make('Cities')
                ->count('clubs.city_id')
                ->labels($this->getCities()),
            DoughnutStatus::make('Statuses')
                ->count('clubs.status')
                ->labels(Club::getConstOptions('statuses'))
                ->colors(self::STATUS_BADGES),
            DoughnutStatus::make('Traffics')
                ->count('clubs.traffic')
                ->labels(Club::getConstOptions('traffics'))
                ->colors(self::TRAFFIC_BADGES),
            DoughnutStatus::make('Checkins available')
                ->labels(['No', 'Yes'])
                ->count('clubs.checkin_availability'),
        ];
    }

    protected function getCities()
    {
        return City::active()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
