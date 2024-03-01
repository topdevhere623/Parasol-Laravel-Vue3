<?php

namespace App\ParasolCRMV2\Resources;

use App\Actions\Member\SyncAllAvailableClubsPlanMemberAction;
use App\Models\Area;
use App\Models\BackofficeUser;
use App\Models\Club\Club;
use App\Models\Corporate;
use App\Models\Member\Member;
use App\Models\Member\MemberBillingDetail;
use App\Models\Member\MemberPasskit;
use App\Models\Member\MemberPrimary;
use App\Models\Member\MemberShippingDetail;
use App\Models\Member\MembershipSource;
use App\Models\Member\MembershipType;
use App\Models\PassportLoginHistory;
use App\Models\Plan;
use App\ParasolCRMV2\Fields\LinkedIn;
use Carbon\Carbon;
use donatj\UserAgent\UserAgentParser;
use ParasolCRMV2\Containers\TabElement;
use ParasolCRMV2\Containers\VerticalTab;
use ParasolCRMV2\Fields\Avatar;
use ParasolCRMV2\Fields\BelongsTo;
use ParasolCRMV2\Fields\BelongsToMany;
use ParasolCRMV2\Fields\Boolean;
use ParasolCRMV2\Fields\CascadeSelect;
use ParasolCRMV2\Fields\Date;
use ParasolCRMV2\Fields\DateTime;
use ParasolCRMV2\Fields\Email;
use ParasolCRMV2\Fields\HasMany;
use ParasolCRMV2\Fields\HasOne;
use ParasolCRMV2\Fields\Hidden;
use ParasolCRMV2\Fields\Password;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\ResourceScheme;

class MemberPrimaryResource extends ResourceScheme
{
    public static $model = MemberPrimary::class;

    public const MEMBERSHIP_STATUS_BADGES = [
        'active' => 'green',
        'expired' => 'gray',
        'cancelled' => 'orange',
        'processing' => 'blue',
        'transferred' => 'blue',
        'paused' => 'red',
        'payment_defaulted_on_hold' => 'magenta',
    ];

    public function fields(): array
    {
        return [
            Text::make('program_id', 'Program')
                ->displayHandler(fn ($record) => optional($record->program)->name)
                ->url(fn ($record) => '/programs/'.optional($record->program)->id)
                ->sortable()
                ->onlyOnTable(),
            Text::make('package_id', 'Package')
                ->displayHandler(fn ($record) => optional($record->package)->title)
                ->url(fn ($record) => '/packages/'.optional($record->program)->id)
                ->hideOnTable()
                ->sortable()
                ->onlyOnTable(),
            Text::make('plan_id', 'Plan')
                ->displayHandler(fn ($record) => optional($record->plan)->title)
                ->url(fn ($record) => '/plans/'.optional($record->plan)->id)
                ->sortable()
                ->onlyOnTable(),
            Text::make('member_id')
                ->sortable(),
            Select::make('membership_status')
                ->options(MemberPrimary::getConstOptions('membership_statuses'))
                ->rules('required')
                ->default(MemberPrimary::MEMBERSHIP_STATUSES['active'])
                ->badges(static::MEMBERSHIP_STATUS_BADGES)
                ->sortable(),
            Date::make('start_date', 'Join date')
                ->sortable(),
            Date::make('end_date', 'Expiry')
                ->sortable(),
            Text::make('full_name', 'Full name')
                ->computed()
                ->setFromRecordHandler(function ($values) {
                    return $values['first_name'].' '.$values['last_name'];
                })
                ->unfillableRecord()
                ->onlyOnTable(),
            Text::make('first_name')
                ->hideOnTable(),
            Text::make('last_name')
                ->hideOnTable(),
            Select::make('membership_type_id', 'Membership type')
                ->rules('required')
                ->options($this->getMembershipTypes())
                ->sortable(),
            Email::make('email', 'Personal email')
                ->hideOnTable(),
            Email::make('recovery_email', 'Recovery email')
                ->hideOnTable(),
            Select::make('membership_source_id', 'Membership source')
                ->options($this->getMembershipSources())
                ->onlyOnForm(),
            BelongsToMany::make(
                'membershipDurations',
                MemberShippingDetail::class,
                'membershipDurations',
                'Membership Durations'
            )
                ->titleField('title')
                ->multiple()
                ->onlyOnForm(),

            BelongsTo::make('corporate', Corporate::class, 'corporate', 'Membership corporate')
                ->sortable(),
            Text::make('phone', 'Phone number')
                ->onlyOnForm(),
            Boolean::make('linkedin_verified', 'LinkedIn verified')
                ->onlyOnForm(),
            LinkedIn::make('linkedin_url', 'LinkedIn URL')
                ->onlyOnForm(),
            BelongsTo::make('bdmBackofficeUser', BackofficeUser::class)
                ->label('BDM')
                ->optionHandler(fn () => BackofficeUser::getSelectable())
                ->onlyOnForm(),
            Date::make('dob', 'Date of birth')
                ->onlyOnForm(),
            BelongsTo::make('area', Area::class)
                ->optionHandler(
                    fn ($query) => $query->leftJoinRelationship('city')->selectRaw(
                        'CONCAT_WS(": ", cities.name, areas.name) as name, areas.id'
                    )
                )
                ->titleField('name')
                ->onlyOnForm(),
            Email::make('login_email', 'Login email')
                ->hideOnTable()
                ->onlyOnTable(),
            Select::make('main_email', 'Login')
                ->options(MemberPrimary::LOGIN)
                ->badges(MemberPrimary::LOGIN_BADGES)
                ->rules(['required'])
                ->formOptions(function ($record, $options) {
                    $options = [
                        'personal_email' => 'Personal email: '.$record->email,
                        'recovery_email' => 'Recovery email: '.$record->recovery_email,
                    ];
                    return $options;
                })->onlyOnForm(),
            Password::make('password')
                ->withMeta(['generate' => true])
                ->onlyOnForm(),
            DateTime::make('password_created_at', 'Password created')
                ->unfillableRecord()
                ->onlyOnForm(),
            Text::make('referral_code')
                ->onlyOnForm(),
            Text::make('offer_code')
                ->onlyOnForm(),
            Avatar::make('avatar', 'Profile Photo')
                ->username('first_name')
                ->onlyOnForm(),
            Select::make('plan_id', 'Plan')
                ->rules('required')
                ->options($this->getPlans())
                ->onlyOnForm(),
            BelongsToMany::make('checkinAvailableClubs', Club::class, 'checkinAvailableClubs', 'Clubs')
                ->multiple()
                ->optionHandler(fn ($query) => $query->checkinAvailable())
                ->updateRelatedHandler(function (Member $record, $relation, BelongsToMany $field) {
                    if ($record->plan?->allowed_club_type == Plan::ALLOWED_CLUB_TYPES['all_available']) {
                        (new SyncAllAvailableClubsPlanMemberAction())->handle($record, $field->getIds());
                        return;
                    }
                    $field->updateRelated($record);
                })
                ->onlyOnForm(),
            BelongsToMany::make('favoriteClubs', Club::class, 'favoriteClubs', 'Favorite clubs')
                ->multiple()
                ->optionHandler(fn ($query) => $query->checkinAvailable())
                ->onlyOnForm(),

            HasOne::make('memberBillingDetail', MemberBillingDetail::class, 'memberBillingDetail', 'Billing Detail')
                ->fields([
                    Text::make('first_name'),
                    Text::make('last_name'),
                    BelongsTo::make('corporate', Corporate::class, null, 'Membership corporate')
                        ->sortable(),
                    CascadeSelect::make('country_id', 'Country')
                        ->endpoint('/location/getCountries'),
                    Text::make('city'),
                    Text::make('state'),
                    Text::make('street'),
                    Boolean::make('is_gift'),
                ])
                ->onlyOnForm(),

            HasOne::make('passKit', MemberPasskit::class, 'passKit', 'Pass Info')
                ->fields([
                    Text::make('passkit_url')
                        ->setFromRecordHandler(fn ($record) => $record->pass_url)
                        ->unfillableRecord(),
                    Text::make('passkit_id')->unfillableRecord(),
                    Text::make('status')->unfillableRecord(),
                    Boolean::make('has_apple_installed')->unfillableRecord(),
                    Boolean::make('has_google_installed')->unfillableRecord(),
                    Boolean::make('has_apple_uninstalled')->unfillableRecord(),
                    Boolean::make('has_google_uninstalled')->unfillableRecord(),
                ])
                ->unfillableRecord()
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
            Hidden::make('renewal_url')
                ->unfillableRecord()
                ->setFromRecordHandler(function (Member $record) {
                    return $record->pendingMembershipRenewal?->renewal_url ?? ($record->member_type == Member::MEMBER_TYPES['member'] ? '' : null);
                }),
        ];
    }

    public function layout(): array
    {
        return [
            VerticalTab::make('member')->attach([
                TabElement::make('Membership details')->attach([
                    'member_id',
                    'membership_status',
                    'first_name',
                    'last_name',
                    'avatar',
                    'membership_type_id',
                    'email',
                    'recovery_email',
                    'start_date',
                    'end_date',
                    'membership_source_id',
                    'corporate',
                    'phone',
                    'linkedin_verified',
                    'linkedin_url',
                    'bdmBackofficeUser',
                    'dob',
                    'area',
                    'membershipDurations',
                ]),
                TabElement::make('Login details')->attach([
                    'password_created_at',
                    'main_email',
                    'password',
                ]),
                TabElement::make('Referral & offer codes')->attach([
                    'referral_code',
                    'offer_code',
                ]),
                TabElement::make('Plan and Clubs')->attach([
                    'plan_id',
                    'checkinAvailableClubs',
                    'favoriteClubs',
                ]),
                TabElement::make('Billing details')->attach([
                    'memberBillingDetail',
                ]),
                TabElement::make('PassKit')->attach([
                    'passKit',
                ]),
                TabElement::make('Login histories')->attach([
                    'passportLoginHistories',
                ]),
            ]),
        ];
    }

    public static function label(): string
    {
        return 'Primary Member';
    }

    public static function singularLabel(): string
    {
        return 'Primary Member';
    }

    protected function getMembershipTypes(): array
    {
        return MembershipType::orderBy('title')
            ->pluck('title', 'id')
            ->toArray();
    }

    protected function getMembershipSources(): array
    {
        return MembershipSource::sort()
            ->pluck('title', 'id')
            ->toArray();
    }

    protected function getPlans(): array
    {
        return Plan::orderBy('title')
            ->pluck('title', 'id')
            ->toArray();
    }
}
