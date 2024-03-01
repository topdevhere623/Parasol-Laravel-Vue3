<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Partner\Partner;
use App\Models\Partner\PartnerContact;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Containers\Group;
use ParasolCRMV2\Fields\BelongsToMany;
use ParasolCRMV2\Fields\Email;
use ParasolCRMV2\Fields\Phone;
use ParasolCRMV2\Fields\Select;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Fields\Textarea;
use ParasolCRMV2\Filters\Fields\MultipleSelectFilterField;
use ParasolCRMV2\Filters\Fields\TextFilterField;
use ParasolCRMV2\Filters\InFilter;
use ParasolCRMV2\Filters\LikeFilter;
use ParasolCRMV2\ResourceScheme;

class PartnerContactResource extends ResourceScheme
{
    public static $model = PartnerContact::class;

    public static $defaultSortBy = 'contact';

    public static $defaultSortDirection = 'ASC';

    public function tableQuery(Builder $query)
    {
        $query->with('partners')
            ->leftJoin(
                'partner_partner_contact',
                'partner_contacts.id',
                '=',
                'partner_partner_contact.partner_contact_id'
            );
    }

    public function fields(): array
    {
        return [
            Text::make('contact')
                ->rules('required')
                ->sortable(),
            Email::make('email')
                ->sortable(),
            Text::make('job_role')
                ->sortable(),
            Phone::make('phone')
                ->sortable(),
            Select::make('type')
                ->options(PartnerContact::getConstOptions('types'))
                ->default(PartnerContact::TYPES['primary'])
                ->sortable(),
            Textarea::make('notes')
                ->onlyOnForm(),
            BelongsToMany::make('partners', Partner::class)
                ->titleField('name')
                ->onlyOnForm(),
            Text::make('partners')
                ->computed()
                ->displayHandler(function (PartnerContact $record) {
                    return $record->partners()->pluck('name')->implode(', ');
                })
                ->onlyOnTable(),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('Basic details')->attach([
                'partners',
                'contact',
                'email',
                'phone',
                'type',
                'job_role',
                'notes',
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'contact', 'partner_contacts.contact')->quick(),
            LikeFilter::make(TextFilterField::make(), 'email', 'partner_contacts.email')->quick(),
            LikeFilter::make(TextFilterField::make(), 'job_role', 'partner_contacts.job_role')->quick(),
            LikeFilter::make(TextFilterField::make(), 'phone', 'partner_contacts.phone')->quick(),

            InFilter::make(
                MultipleSelectFilterField::make()->options(PartnerContact::getConstOptions('types')),
                'type',
                'partner_contacts.type',
            )->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()->options($this->getPartners()),
                'partner',
                'partner_partner_contact.partner_id',
            ),
        ];
    }

    protected function getPartners(): array
    {
        return Partner::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
