<?php

namespace App\ParasolCRM\Resources;

use App\Models\Package;
use App\Models\PassportLoginHistory;
use App\Models\Plan;
use App\Models\Program;
use App\Models\Referral;
use App\Models\WebSite\Page;
use Carbon\Carbon;
use donatj\UserAgent\UserAgentParser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as BelongsToManyRelation;
use ParasolCRM\Containers\TabElement;
use ParasolCRM\Containers\VerticalTab;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\BelongsToMany;
use ParasolCRM\Fields\Boolean;
use ParasolCRM\Fields\Color;
use ParasolCRM\Fields\DateTime;
use ParasolCRM\Fields\Editor;
use ParasolCRM\Fields\Email;
use ParasolCRM\Fields\HasMany;
use ParasolCRM\Fields\Hidden;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Media;
use ParasolCRM\Fields\Multiselect;
use ParasolCRM\Fields\Password;
use ParasolCRM\Fields\Select;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class ProgramResource extends ResourceScheme
{
    public static $model = Program::class;

    public const STATUS_BADGES = [
        'active' => 'green',
        'inactive' => 'gray',
    ];

    public function fields(): array
    {
        return [
            HorizontalRadioButton::make('status')
                ->options(Program::getConstOptions('statuses'))
                ->rules('required')
                ->default(Program::STATUSES['active'])
                ->badges(self::STATUS_BADGES)
                ->sortable(),
            Text::make('name')
                ->rules('required')
                ->sortable(),
            Text::make('public_name')
                ->rules('required')
                ->sortable(),
            Boolean::make('generate_passes')
                ->sortable(),
            Text::make('passkit_id', 'PassKit ID')
                ->dependsOn('generate_passes', 0, ['hide'])
                ->onlyOnForm(),
            Text::make('prefix', 'Card Prefix')
                ->rules('required')
                ->sortable(),
            Media::make('member_portal_logo'),
            Color::make('member_portal_main_color')
                ->default('#e0b050')
                ->onlyOnForm(),
            Text::make('passkit_faq_url_ios', 'PassKit FAQ URL iOS')->rules(['required_if:generate_passes,1'])
                ->default('https://advplus.ae/uploads/documents/digital-membership-card-guide-ios.pdf')
                ->dependsOn('generate_passes', 0, ['hide'])
                ->onlyOnForm(),
            Text::make('passkit_faq_url_android', 'PassKit FAQ URL Android')->rules(['required_if:generate_passes,1'])
                ->default('https://advplus.ae/uploads/documents/digital-membership-card-guide-android.pdf')
                ->dependsOn('generate_passes', 0, ['hide'])
                ->onlyOnForm(),
            Boolean::make('passkit_button_on_top', 'Show pass button on top (member portal)')
                ->dependsOn('generate_passes', 0, ['hide'])
                ->onlyOnForm(),
            Boolean::make('has_access_clubs')
                ->default(true)
                ->onlyOnForm(),
            Boolean::make('has_access_about_membership')
                ->default(true)
                ->onlyOnForm(),
            Boolean::make('has_access_profile')
                ->default(true)
                ->onlyOnForm(),
            Boolean::make('has_access_offers')
                ->default(true)
                ->onlyOnForm(),
            Boolean::make('has_access_visiting_family_membership')
                ->default(true)
                ->onlyOnForm(),
            Boolean::make('has_access_password_change')
                ->default(true)
                ->onlyOnForm(),
            Boolean::make('has_access_contact_us')
                ->default(true)
                ->onlyOnForm(),
            Boolean::make('has_access_all_clubs')
                ->default(false)
                ->onlyOnForm(),
            Text::make('terms_and_conditions_url')
                ->placeholder(Page::getProtectedPageUrl('terms-and-conditions'))
                ->onlyOnForm(),
            Text::make('faq_page_url')
                ->placeholder(route('faq.index'))
                ->onlyOnForm(),
            Text::make('club_guide_url')
                ->placeholder(route('detailed_club_info_doc'))
                ->onlyOnForm(),
            Text::make('whatsapp_url')
                ->onlyOnForm(),
            Editor::make('contact_us_page')->dependsOn('has_access_contact_us', true),
            Media::make('website_logo'),
            Color::make('booking_first_main_color', 'First main')
                ->emptyColor('#00b8dd')
                ->onlyOnForm(),
            Color::make('booking_second_main_color', 'Second main')
                ->emptyColor('#2a87a9')
                ->onlyOnForm(),
            Color::make('booking_headers_color', 'Headers')
                ->emptyColor('#37B1BF')
                ->onlyOnForm(),
            Color::make('booking_second_headers_color', 'Second headers')
                ->emptyColor('#37b1bf')
                ->onlyOnForm(),
            Color::make('booking_coupon_button_color', 'Coupon button')
                ->emptyColor('#ffa400')
                ->onlyOnForm(),
            Color::make('booking_confirm_button_color', 'Confirm button')
                ->emptyColor('#006fbb')
                ->onlyOnForm(),
            Color::make('booking_clubs_select_color', 'Clubs select')
                ->emptyColor('#68bb49')
                ->onlyOnForm(),
            Color::make('booking_button_text_color', 'Button text')
                ->emptyColor('#ffffff')
                ->onlyOnForm(),
            Color::make('booking_total_color', 'Total')
                ->emptyColor('#109c91')
                ->onlyOnForm(),
            HasMany::make(
                'passportLoginHistories',
                PassportLoginHistory::class,
                'passportLoginHistories',
                'Login histories'
            )
                ->fields([
                    Text::make('user_agent')
                        ->setFromRecordHandler(function ($record) {
                            $parser = new UserAgentParser();
                            $ua = $parser->parse($record->user_agent);
                            return $ua->platform().' '.$ua->browser().' '.$ua->browserVersion();
                        })
                        ->unfillableRecord(),
                    DateTime::make('created_at', 'Login datetime')
                        ->setFromRecordHandler(function ($record) {
                            return Carbon::parse($record->created_at)->format(config('app.DATETIME_FORMAT'));
                        })
                        ->unfillableRecord(),
                ])
                ->unfillableRecord()
                ->onlyOnForm(),
            Boolean::make('has_access_referrals')
                ->default(true)
                ->onlyOnForm(),
            Select::make('referral_amount_type', 'Discount amount type')
                ->options(Program::getConstOptions('amount_types'))
                ->default(Program::DEFAULT_REFERRAL_AMOUNT_TYPE)
                ->dependsOn('has_access_referrals', true)
                ->onlyOnForm(),
            Text::make('referral_amount', 'Discount amount')
                ->default(Program::DEFAULT_REFERRAL_AMOUNT)
                ->dependsOn('has_access_referrals', true)
                ->onlyOnForm(),
            Text::make('referral_code_template', 'Code template')
                ->tooltip(
                    '{n} - where is <strong>n</strong> = number of random symbols <br/> Example: AD-{2} gives on generating AD-f5',
                    true
                )
                ->default(Program::DEFAULT_REFERRAL_CODE_TEMPLATE)
                ->dependsOn('has_access_referrals', true)
                ->onlyOnForm(),
            Editor::make('referrals_page')
                ->dependsOn('has_access_referrals', true)
                ->onlyOnForm(),
            Media::make('referrals_page_img', 'Referrals page image')
                ->dependsOn('has_access_referrals', true)
                ->onlyOnForm(),
            HorizontalRadioButton::make('exclude_or_include', 'Exclude or include')
                ->options(Program::getConstOptions('referral_plan_types'))
                ->default(Program::REFERRAL_PLAN_TYPES['include'])
                ->setFromRecordHandler(function ($record) {
                    return $record->excludedPlans()->count()
                        ? Program::REFERRAL_PLAN_TYPES['exclude']
                        : Program::REFERRAL_PLAN_TYPES['include'];
                })
                ->dependsOn('has_access_referrals', true)
                ->onlyOnForm()
                ->computed()
                ->unfillableRecord()
                ->onlyOnForm(),
            BelongsToMany::make('excludedPlans', Plan::class, 'excludedPlans', 'Excluded plans')
                ->updateRelatedHandler(
                    function (
                        $record,
                        BelongsToManyRelation $relation,
                        BelongsToMany $field
                    ) {
                        if (request('exclude_or_include') == Program::REFERRAL_PLAN_TYPES['exclude']) {
                            $record->includedPlans()->detach();
                            $relation->syncWithPivotValues(
                                $field->getIds(),
                                ['type' => Program::REFERRAL_PLAN_TYPES['exclude']]
                            );
                        }
                    }
                )
                ->dependsOn('exclude_or_include', Program::REFERRAL_PLAN_TYPES['exclude'])
                ->dependentBehavior()
                ->multiple()
                ->optionHandler(
                    function (Builder $query, $record) {
                        $currentSelectedPlans = $record->excludedPlans->pluck('id')->toArray();
                        return $query->active()->when($currentSelectedPlans, fn (Builder $query) => $query->orWhereIn(
                            'id',
                            $currentSelectedPlans
                        ));
                    }
                )
                ->onlyOnForm(),
            BelongsToMany::make('includedPlans', Plan::class, 'includedPlans', 'Included plans')
                ->updateRelatedHandler(
                    function (
                        $record,
                        BelongsToManyRelation $relation,
                        BelongsToMany $field
                    ) {
                        if (request('exclude_or_include') == Program::REFERRAL_PLAN_TYPES['include']) {
                            $record->excludedPlans()->detach();
                            $relation->syncWithPivotValues(
                                $field->getIds(),
                                ['type' => Program::REFERRAL_PLAN_TYPES['include']]
                            );
                        }
                    }
                )
                ->default(Program::DEFAULT_REFERRAL_PLAN_TYPES)
                ->dependsOn('exclude_or_include', Program::REFERRAL_PLAN_TYPES['include'])
                ->dependentBehavior()
                ->multiple()
                ->optionHandler(
                    function (Builder $query, $record) {
                        $currentSelectedPlans = $record->includedPlans->pluck('id')->toArray();
                        return $query->active()->when($currentSelectedPlans, fn (Builder $query) => $query->orWhereIn(
                            'id',
                            $currentSelectedPlans
                        ));
                    }
                )
                ->onlyOnForm(),

            Multiselect::make('rewards')
                ->default('')
                ->options(Referral::getConstOptions('rewards'))
                ->dependsOn('has_access_referrals', true)
                ->onlyOnForm(),

            // Club Document
            Boolean::make('club_document_available')
                ->tooltip(
                    'Club document will be generated in 3 minutes. Also all documents are regenerated every day at 4 am'
                )
                ->default(false)
                ->onlyOnForm(),
            Boolean::make('club_document_join_today_available')
                ->dependsOn('club_document_available', true)
                ->default(false)
                ->onlyOnForm(),
            BelongsTo::make('clubDocumentMainPagePackage', Package::class)
                ->dependsOn('club_document_available', true)
                ->onlyOnForm(),
            BelongsTo::make('clubDocumentPlan', Plan::class)
                ->dependsOn('club_document_available', true)
                ->onlyOnForm(),
            Text::make('club_document_url', 'Club document')
                ->onlyOnTable()
                ->url(fn (Program $record) => $record->getClubDocsLink())
                ->displayHandler(fn (Program $record) => $record->club_document_available ? 'Download' : ''),
            Hidden::make('club_document_url', 'Club document')
                ->computed()
                ->unfillableRecord()
                ->onlyOnForm()
                ->setFromRecordHandler(fn (Program $record) => $record->getClubDocsLink()),

            // API
            Boolean::make('has_access_api'),
            Email::make('email')
                ->dependsOn('has_access_api', true)
                ->onlyOnForm()
                ->sortable(),
            Password::make('password')
                ->dependsOn('has_access_api', true),
            Text::make('api_key')
                ->onlyOnForm()
                ->dependsOn('has_access_api', true),
            Text::make('webhook_url', 'Webhook URL')
                ->rules('nullable', 'required_if:has_access_api,true', 'url')
                ->onlyOnForm()
                ->dependsOn('has_access_api', true),
            BelongsTo::make('landingPagePlan', Plan::class)
                ->tooltip('Clubs of this plan will be shown on the landing page or shown through the api')
                ->rules('required_if:has_access_api,true')
                ->onlyOnForm()
                ->dependsOn('has_access_api', true),
            BelongsTo::make('apiDefaultPackage', Package::class)
                ->label('API default package')
                ->tooltip('This package will be used as default on booking process through API')
                ->rules('required_if:has_access_api,true')
                ->onlyOnForm()
                ->dependsOn('has_access_api', true),
        ];
    }

    public function layout(): array
    {
        return [

            VerticalTab::make('program')->attach([
                TabElement::make('Basic Information')->attach([
                    'status',
                    'name',
                    'public_name',
                    'prefix',
                    'color',
                ]),
                TabElement::make('PassKit')->attach([
                    'generate_passes',
                    'passkit_id',
                    'passkit_faq_url_ios',
                    'passkit_faq_url_android',
                    'passkit_button_on_top',
                ]),
                TabElement::make('Booking and Website')->attach([
                    'website_logo',
                    'booking_first_main_color',
                    'booking_second_main_color',
                    'booking_headers_color',
                    'booking_second_headers_color',
                    'booking_coupon_button_color',
                    'booking_confirm_button_color',
                    'booking_clubs_select_color',
                    'booking_button_text_color',
                    'booking_total_color',
                ]),
                TabElement::make('Member Portal')->attach([
                    'member_portal_logo',
                    'member_portal_main_color',
                    'whatsapp_url',
                    'terms_and_conditions_url',
                    'faq_page_url',
                    'club_guide_url',
                    'has_access_clubs',
                    'has_access_about_membership',
                    'has_access_profile',
                    'has_access_offers',
                    'has_access_visiting_family_membership',
                    'has_access_password_change',
                    'has_access_contact_us',
                    'contact_us_page',
                    'has_access_all_clubs',
                ]),
                TabElement::make('Referrals')->attach([
                    'has_access_referrals',
                    'referrals_page',
                    'referrals_page_img',
                    'referral_amount_type',
                    'referral_amount',
                    'referral_code_template',
                    'rewards',
                    'exclude_or_include',
                    'excludedPlans',
                    'includedPlans',
                ]),
                TabElement::make('API')->attach([
                    'has_access_api',
                    'email',
                    'password',
                    'api_key',
                    'webhook_url',
                    'landingPagePlan',
                    'apiDefaultPackage',
                ]),
                TabElement::make('Club Document')->attach([
                    'club_document_available',
                    'club_document_join_today_available',
                    'clubDocumentMainPagePackage',
                    'clubDocumentPlan',
                ]),
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'name')
                ->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(Program::getConstOptions('statuses')),
                'status'
            )
                ->quick(),

        ];
    }

    // TODO: no usages
    public function getPrograms()
    {
        return Program::pluck('name', 'id') ?? [];
    }
}
