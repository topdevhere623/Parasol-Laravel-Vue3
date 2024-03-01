<?php

namespace App\ParasolCRM\Resources;

use App\Models\BackofficeUser;
use App\Models\BackofficeUserProgramAdmin;
use App\Models\Club\BackofficeUserClubAdmin;
use App\Models\Club\Club;
use App\Models\Laratrust\Role;
use App\Models\Program;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\Avatar;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\BelongsToMany;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\Email;
use ParasolCRM\Fields\HorizontalRadioButton;
use ParasolCRM\Fields\Money;
use ParasolCRM\Fields\Number;
use ParasolCRM\Fields\Password;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class BackofficeUserResource extends ResourceScheme
{
    public static $model = BackofficeUser::class;

    public const STATUS_BADGES = [
        'active' => 'green',
        'inactive' => 'red',
    ];

    public function query(\Illuminate\Database\Eloquent\Builder $query)
    {
        $query->whereHasTeam(static::$model::TEAM);
    }

    public function fields(): array
    {
        return [
            Avatar::make('avatar')
                ->username('first_name'),
            HorizontalRadioButton::make('status')
                ->options(BackofficeUser::getConstOptions('statuses'))
                ->badges(static::STATUS_BADGES)
                ->default(1)
                ->sortable(),
            Text::make('full_name')
                ->column('first_name')
                ->sortable()
                ->onlyOnTable()
                ->displayHandler(fn ($record) => $record->full_name),
            Text::make('first_name')
                ->rules(['required', 'string', 'max:255'])
                ->hideOnTable()
                ->sortable(),
            Text::make('last_name')
                ->hideOnTable()
                ->sortable(),
            Email::make('email')
                ->rules(['required', 'string', 'email', 'max:255', 'unique:backoffice_users,email'])
                ->sortable(),
            Password::make('password')
                ->withMeta(['generate' => true])
                ->rules(['nullable', 'string', 'min:8', 'confirmed'])
                ->creationRules(['required']),
            Password::make('password_confirmation', 'Password confirmation')
                ->rules(['nullable', 'string', 'min:8'])
                ->creationRules(['required'])
                ->unfillableRecord(),

            Date::make('created_at', 'Registered')
                ->onlyOnTable()
                ->sortable(),

            BelongsToMany::make('roles', Role::class, 'roles')
                ->onlyOnForm()
                ->titleField('display_name')
                ->hasAccess(function () {
                    return $this->isAdmin();
                }),

            Text::make('nocrm_id', 'NoCRM ID')
                ->onlyOnForm()
                ->hasAccess($this::$model == BackofficeUser::class)
                ->sortable(),

            Number::make('sales_units_target')
                ->hasAccess($this::$model == BackofficeUser::class)
                ->default(0)
                ->sortable(),
            Number::make('weekly_sales_units_target')
                ->hasAccess($this::$model == BackofficeUser::class)
                ->default(0)
                ->sortable(),

            Money::make('sales_revenue_target')
                ->hasAccess($this::$model == BackofficeUser::class)
                ->default(0)
                ->sortable(),

            Money::make('weekly_sales_revenue_target')
                ->hasAccess($this::$model == BackofficeUser::class)
                ->default(0)
                ->sortable(),

            Number::make('renewal_target_percent')
                ->hasAccess($this::$model == BackofficeUser::class)
                ->default(0)
                ->sortable(),
            Number::make('weekly_renewal_target_percent')
                ->hasAccess($this::$model == BackofficeUser::class)
                ->default(0)
                ->sortable(),

            BelongsTo::make('club', Club::class)
                ->sortable()
                ->url('/clubs/{club_id}')
                ->hasAccess($this::$model == BackofficeUserClubAdmin::class)
                ->rules('required'),
            BelongsTo::make('program', Program::class)
                ->titleField('name')
                ->sortable()
                ->url('/program/{program_id}')
                ->hasAccess($this::$model == BackofficeUserProgramAdmin::class)
                ->rules('required'),

            //            HasMany::make('passportLoginHistories', PassportLoginHistory::class, 'passportLoginHistories', 'Login histories')
            //                ->fields([
            //                    Text::make('user_agent')
            //                        ->setFromRecordHandler(function ($record) {
            //                            $parser = new UserAgentParser();
            //                            $ua = $parser->parse($record->user_agent);
            //                            return $ua->platform() .' '. $ua->browser() .' '. $ua->browserVersion();
            //                        })
            //                        ->unfillableRecord(),
            //                    DateTime::make('created_at', 'Login datetime')
            //                        ->setFromRecordHandler(function ($record) {
            //                            return Carbon::parse($record->created_at)->format(config('app.DATETIME_FORMAT'));
            //                        })
            //                        ->unfillableRecord(),
            //                ])
            //                ->unfillableRecord()
            //                ->onlyOnForm(),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('Account')->attach([
                'email',
                'password',
                'password_confirmation',
                'roles',
            ]),
            Group::make('Basic information')->attach([
                'status',
                'avatar',
                'first_name',
                'last_name',
                'club',
                'program',
                'nocrm_id',
                'sales_units_target',
                'weekly_sales_units_target',
                'sales_revenue_target',
                'weekly_sales_revenue_target',
                'renewal_target_percent',
                'weekly_renewal_target_percent',
            ]),
            Group::make('Login histories')->attach([
                'passportLoginHistories',
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            InFilter::make(
                MultipleSelectFilterField::make()
                    ->options(BackofficeUser::getConstOptions('statuses')),
                'status',
                'backoffice_users.status'
            ),
            LikeFilter::make(
                TextFilterField::make(),
                'backoffice_users.first_name',
                'first_name',
                'First name'
            )->quick(),
            LikeFilter::make(
                TextFilterField::make(),
                'backoffice_users.last_name',
                'last_name',
                'Last name'
            )->quick(),
        ];
    }

    public static function singularLabel(): string
    {
        return 'Administrator';
    }

    public static function label(): string
    {
        return 'Administrators';
    }
}
