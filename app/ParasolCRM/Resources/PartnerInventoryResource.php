<?php

namespace App\ParasolCRM\Resources;

use App\Models\Partner\Partner;
use App\Models\Partner\PartnerInventory;
use App\Models\Partner\PartnerInventoryFile;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\File;
use ParasolCRM\Fields\HasMany;
use ParasolCRM\Fields\Money;
use ParasolCRM\Fields\Text;
use ParasolCRM\Fields\Textarea;
use ParasolCRM\Filters\BetweenFilter;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;

class PartnerInventoryResource extends ResourceScheme
{
    public static $model = PartnerInventory::class;

    public static $defaultSortBy = 'model';

    public static $defaultSortDirection = 'ASC';

    public function tableQuery(Builder $query)
    {
        $query->with('partner');
    }

    public function fields(): array
    {
        return [
            Text::make('model')
                ->rules('required')
                ->sortable(),
            Text::make('serial_number')
                ->sortable(),
            Money::make('price')
                ->rules('required')
                ->sortable(),
            Date::make('purchase_date')
                ->sortable(),
            Date::make('installation_date')
                ->sortable(),
            Date::make('returned_to_parasol')
                ->sortable(),
            Textarea::make('login_details')
                ->onlyOnForm(),
            BelongsTo::make('partner', Partner::class)
                ->titleField('name')
                ->url('/partners/{partner_id}'),
            HasMany::make('files', PartnerInventoryFile::class, 'files', 'Attachment files')
                ->fields([
                    File::make('file')
                        ->rules('required'),
                ])
                ->repeaterTitle('file')
                ->onlyOnForm(),
        ];
    }

    public function layout(): array
    {
        return [
            Group::make('Basic details')->attach([
                'model',
                'serial_number',
                'price',
                'partner',
                'purchase_date',
                'installation_date',
                'returned_to_parasol',
                'login_details',
            ]),
            Group::make('files')->attach([
                'files',
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'partner_inventories.serial_number', null, 'Serial Number')
                ->quick(),
            LikeFilter::make(TextFilterField::make(), 'partner_inventories.model', null, 'Model')->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()->options($this->getPartners()),
                'partner',
                'partner_inventories.partner_id'
            ),

            BetweenFilter::make(
                new TextFilterField(),
                new TextFilterField(),
                'partner_inventories.price',
                null,
                'Price'
            )
                ->quick(),
            BetweenFilter::make(
                new TextFilterField(),
                new TextFilterField(),
                'purchase_date',
                'partner_inventories.purchase_date'
            ),
            BetweenFilter::make(
                new TextFilterField(),
                new TextFilterField(),
                'installation_date',
                'partner_inventories.installation_date'
            ),
            BetweenFilter::make(
                new TextFilterField(),
                new TextFilterField(),
                'returned_to_parasol',
                'partner_inventories.returned_to_parasol',
                'Return To Parasol Date'
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
