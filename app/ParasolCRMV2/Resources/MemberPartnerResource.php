<?php

namespace App\ParasolCRMV2\Resources;

use App\Actions\Member\SyncAllAvailableClubsPlanMemberAction;
use App\Models\Area;
use App\Models\Club\Club;
use App\Models\Corporate;
use App\Models\Member\Member;
use App\Models\Member\MemberPasskit;
use App\Models\Member\MemberShippingDetail;
use App\Models\Member\MembershipSource;
use App\Models\Member\MembershipType;
use App\Models\Member\Partner;
use App\Models\PassportLoginHistory;
use App\Models\Plan;
use Carbon\Carbon;
use donatj\UserAgent\UserAgentParser;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Fields\Avatar;
use ParasolCRMV2\Fields\BelongsTo;
use ParasolCRMV2\Fields\BelongsToMany;
use ParasolCRMV2\Fields\Boolean;
use ParasolCRMV2\Fields\Date;
use ParasolCRMV2\Fields\DateTime;
use ParasolCRMV2\Fields\Email;
use ParasolCRMV2\Fields\HasMany;
use ParasolCRMV2\Fields\HasOne;
use ParasolCRMV2\Fields\Hidden;
use ParasolCRMV2\Fields\Password;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\EqualFilter;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\ResourceScheme;

class MemberPartnerResource extends ResourceScheme
{
    public static $model = Partner::class;

    public function fields(): array
    {
        return [
            Hidden::make('parent_id'),
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
                ->hideOnTable()
                ->sortable()
                ->onlyOnTable(),

            Text::make('parent_id', 'Primary member')
                ->displayHandler(fn ($record) => optional($record->member)->full_name)
                ->url('/member-primary/{parent_id}')
                ->sortable()
                ->onlyOnTable(),

            Text::make('member_id')
                ->sortable(),
            Select::make('membership_status')
                ->options(static::$model::getConstOptions('membership_statuses'))
                ->rules(['required'])
                ->badges(MemberPrimaryResource::MEMBERSHIP_STATUS_BADGES)
                ->sortable(),
            Date::make('start_date', 'Join date'),
            Date::make('end_date', 'Expiry'),
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
            Email::make('email', 'Login email')
                ->rules(['required', 'string', 'email'])
                ->hideOnTable(),
            Select::make('membership_type_id', 'Membership type')
                ->rules('required')
                ->options($this->getMembershipTypes())
                ->sortable(),
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
            Password::make('password')
                ->withMeta(['generate' => true])
                ->onlyOnForm(),
            DateTime::make('password_created_at', 'Password created')
                ->unfillableRecord()
                ->onlyOnForm(),
            Avatar::make('avatar')
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
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('Membership details')->attach([
                'member_id',
                'membership_status',
                'start_date',
                'end_date',
                'first_name',
                'last_name',
                'dob',
                'membership_type_id',
                'membership_source_id',
                'phone',
                'area',
                'membershipDurations',
                'corporate',
            ]),
            Group::make('Login details')->attach([
                'password_created_at',
                'email',
                'password',
            ]),
            Group::make('Gallery')->attach([
                'avatar',
            ]),
            Group::make('Plan and Clubs')->attach([
                'plan_id',
                'checkinAvailableClubs',
                'favoriteClubs',
            ]),
            Group::make('')->attach([
                'passKit',
            ]),
            Group::make('Login histories')->attach([
                'passportLoginHistories',
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            EqualFilter::make(TextFilterField::make(), 'parent_id')
                ->hidden(),
        ];
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
