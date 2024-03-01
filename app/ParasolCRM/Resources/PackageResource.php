<?php

namespace App\ParasolCRM\Resources;

use App\Models\GiftCard;
use App\Models\Package;
use ParasolCRM\Containers\TabElement;
use ParasolCRM\Containers\VerticalTab;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\Boolean;
use ParasolCRM\Fields\Editor;
use ParasolCRM\Fields\Hidden;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Media;
use ParasolCRM\Fields\Text;
use ParasolCRM\Fields\Textarea;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class PackageResource extends ResourceScheme
{
    public static $model = Package::class;

    public const STATUS_BADGES = [
        'active' => 'green',
        'inactive' => 'gray',
    ];

    public const RELATION_TYPE_BADGES = [
        'corporate' => 'green',
        'reseller' => 'blue',
        'b2c' => 'gray',
    ];

    public function fields(): array
    {
        $relationTypes = Package::getConstOptions('relation_types');
        $relationTypes['b2c'] = 'B2C';

        return [
            HorizontalRadioButton::make('status')
                ->options(Package::getConstOptions('statuses'))
                ->badges(self::STATUS_BADGES)
                ->default('active')
                ->rules(['required'])
                ->sortable(),
            HorizontalRadioButton::make('relation_type')
                ->options($relationTypes)
                ->badges(self::RELATION_TYPE_BADGES)
                ->default('b2c')
                ->rules(['required'])
                ->sortable(),
            BelongsTo::make('program', \App\Models\Program::class)
                ->titleField('name')
                ->url('/programs/{program_id}')
                ->sortable()
                ->rules(['required']),

            Text::make('title')
                ->rules(['required'])
                ->sortable(),
            Text::make('slug')
                ->placeholder('Keep empty to autogenerate from a Title')
                ->onlyOnForm(),
            Text::make('price_description', 'Price Description')
                ->sortable(),
            Boolean::make('is_booking_uae_phone', 'Require UAE Phone number on Booking')
                ->sortable()
                ->default(1)
                ->hideOnTable(),
            Boolean::make('hide_membership_source_on_booking')
                ->default(false)
                ->setFromRecordHandler(function (Package $record) {
                    return !!$record->membership_source_id;
                })
                ->unfillableRecord()
                ->onlyOnForm(),
            BelongsTo::make(
                'membershipSource',
                \App\Models\Member\MembershipSource::class,
                'membershipSource',
                'Membership Source'
            )
                ->url('/membership-sources/{membership_source_id}')
                ->dependsOn('hide_membership_source_on_booking', true)
                ->nullable()
                ->rules(['required_if:hide_membership_source_on_booking,true'])
                ->onlyOnForm()
                ->sortable(),
            BelongsTo::make('giftCard', GiftCard::class)
                ->multiple()
                ->onlyOnForm(),
            Media::make('image', 'Checkout photo')
                ->onlyOnForm(),
            Media::make('mobile_image', 'Mobile checkout photo')
                ->onlyOnForm(),
            Editor::make('description')
                ->onlyOnForm()
                ->sortable(),
            Text::make('Link')
                ->column('slug')
                ->displayHandler(fn ($record) => $record->slug)
                ->url(fn ($record) => route('booking.step-1', ['package' => $record->slug]))
                ->badges(['default' => 'blue'])
                ->onlyOnTable(),
            Hidden::make('link')
                ->computed()
                ->setFromRecordHandler(fn ($record) => route('booking.step-1', ['package' => $record->slug]))
                ->unfillableRecord(),

            Boolean::make('show_header')
                ->onlyOnForm()
                ->default(true),
            Boolean::make('show_header_menu')
                ->dependsOn('show_header', false, ['hide'])
                ->onlyOnForm()
                ->default(true),
            Boolean::make('show_header_member_portal_link')
                ->dependsOn('show_header', false, ['hide'])
                ->onlyOnForm()
                ->default(true),
            Boolean::make('show_footer')
                ->onlyOnForm()
                ->default(true),

            Boolean::make('show_footer_description')
                ->dependsOn('show_footer', false, ['hide'])
                ->onlyOnForm()
                ->default(true),

            Boolean::make('show_footer_navigation')
                ->dependsOn('show_footer', false, ['hide'])
                ->onlyOnForm()
                ->default(true),

            Boolean::make('show_footer_socials')
                ->dependsOn('show_footer', false, ['hide'])
                ->onlyOnForm()
                ->default(true),
            Boolean::make('show_tawk_chat')
                ->onlyOnForm()
                ->default(true),
            Boolean::make('show_coupons')
                ->onlyOnForm()
                ->default(true),
            Boolean::make('show_clubs')
                ->onlyOnForm()
                ->default(true),
            Boolean::make('show_steps_progress')
                ->onlyOnForm()
                ->default(true),

            Text::make('apply_coupon')
                ->onlyOnForm(),

            Text::make('page_title')
                ->onlyOnForm(),
            Textarea::make('meta_description')
                ->onlyOnForm(),
            Editor::make('complete_message')
                ->tooltip('Keep empty to display a default message')
                ->onlyOnForm(),
            Editor::make('renewal_complete_message')
                ->tooltip('Keep empty to display a default message')
                ->onlyOnForm(),
        ];
    }

    public function layout(): array
    {
        return [
            VerticalTab::make('')->attach([
                TabElement::make('Basic Information')->attach([
                    'status',
                    'relation_type',
                    'program',
                    'title',
                    'slug',
                    'price_description',
                    'membershipSource',
                    'description',
                    'giftCard',
                ]),
                TabElement::make('Display Settings')->attach([
                    'image',
                    'mobile_image',
                    'is_booking_uae_phone',
                    'hide_membership_source_on_booking',
                    'show_header',
                    'show_header_menu',
                    'show_header_member_portal_link',
                    'show_footer',
                    'show_footer_description',
                    'show_footer_navigation',
                    'show_footer_socials',
                    'show_tawk_chat',
                    'show_coupons',
                    'show_clubs',
                    'show_steps_progress',
                    'apply_coupon',
                ]),
                TabElement::make('Booking completed messages')->attach([
                    'complete_message',
                    'renewal_complete_message',
                ]),
                TabElement::make('SEO')->attach([
                    'page_title',
                    'meta_description',
                ]),
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'title', 'packages.title')
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Package::getConstOptions('statuses')),
                'packages.status'
            )
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Package::getConstOptions('relation_types')),
                'packages.relation_type'
            )
                ->quick(),

        ];
    }
}
