<?php

namespace App\ParasolCRM\Resources;

use App\Models\BackofficeUser;
use App\Models\SalesQuote;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\Boolean;
use ParasolCRM\Fields\Email;
use ParasolCRM\Fields\Number;
use ParasolCRM\Fields\Phone;
use ParasolCRM\Fields\Select;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\EqualFilter;
use ParasolCRM\Filters\Fields\SelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class SalesQuoteResource extends ResourceScheme
{
    private const FIELD_TITLES = [
        'clubs_count' => 'No. of Clubs',
        'singles_count' => 'No. of Singles',
        'families_count' => 'No. of Families',
    ];

    private const MONTHS_TO_DAYS = [
        '0.25' => 'One week',
        '0.5' => 'Half month',
        '1' => '1 month',
        '2' => '2 months',
        '3' => '3 months',
        '4' => '4 months',
        '6' => '6 months',
        '12' => 'One year',
    ];

    public static $model = SalesQuote::class;

    public function tableQuery(Builder $query)
    {
        $query->with('salesPerson');
    }

    public function fields(): array
    {
        return [
            Text::make('corporate_client')
                ->rules('required')
                ->sortable()
                ->hasAccess(fn () => $this->checkAccess()),
            Text::make('corporate_contact_name')
                ->sortable()
                ->hasAccess(fn () => $this->checkAccess()),
            Email::make('corporate_contact_email')->sortable(),
            Phone::make('corporate_contact_number')
                ->sortable()
                ->hasAccess(fn () => $this->checkAccess()),
            BelongsTo::make('salesPerson', BackofficeUser::class)
                ->optionHandler(fn () => BackofficeUser::getSelectable())
                ->titleField('first_name')
                ->displayHandler(fn ($record) => $record->salesPerson?->full_name)
                ->rules('required')
                ->url('/admins/{sales_person_id}')
                ->sortable(),

            Number::make('clubs_count', self::FIELD_TITLES['clubs_count'])
                ->sortable()
                ->rules('required')
                ->hasAccess(fn () => $this->checkAccess()),
            Number::make('singles_count', self::FIELD_TITLES['singles_count'])
                ->sortable()
                ->rules('required')
                ->hasAccess(fn () => $this->checkAccess()),
            Number::make('families_count', self::FIELD_TITLES['families_count'])
                ->sortable()
                ->rules('required')
                ->hasAccess(fn () => $this->checkAccess()),
            Select::make('duration')
                ->options(self::MONTHS_TO_DAYS)
                ->rules('required')
                ->hasAccess(fn () => $this->checkAccess()),
            Boolean::make('display_monthly_value'),
            Boolean::make('display_daily_per_club'),
        ];
    }

    private function checkAccess()
    {
        return !request()->route('id') || $this->isAdmin();
    }

    public function layout(): array
    {
        return [
            Group::make('Sale Quote details')
                ->attach([
                    'corporate_client',
                    'corporate_contact_name',
                    'corporate_contact_email',
                    'corporate_contact_number',
                    'salesPerson',
                    'clubs_count',
                    'singles_count',
                    'families_count',
                    'duration',
                    'display_monthly_value',
                ]),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'corporate_client')->quick(),

            EqualFilter::make(
                TextFilterField::make(),
                'clubs_count',
                null,
                self::FIELD_TITLES['clubs_count']
            )->quick(),

            EqualFilter::make(
                TextFilterField::make(),
                'singles_count',
                null,
                self::FIELD_TITLES['singles_count']
            )->quick(),

            EqualFilter::make(
                TextFilterField::make(),
                'families_count',
                null,
                self::FIELD_TITLES['families_count']
            )->quick(),

            EqualFilter::make(
                SelectFilterField::make()->options(self::MONTHS_TO_DAYS),
                'duration'
            )
                ->quick(),

            EqualFilter::make(
                SelectFilterField::make()
                    ->options(BackofficeUser::getSelectable()),
                'sales_person',
                'sales_person_id'
            )
                ->quick(),
        ];
    }
}
