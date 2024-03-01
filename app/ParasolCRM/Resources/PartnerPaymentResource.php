<?php

namespace App\ParasolCRM\Resources;

use App\Models\Partner\Partner;
use App\Models\Partner\PartnerPayment;
use App\Models\Partner\PartnerPaymentFile;
use App\Models\Partner\PartnerTranche;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\Containers\Group;
use ParasolCRM\Fields\BelongsTo;
use ParasolCRM\Fields\Date;
use ParasolCRM\Fields\File;
use ParasolCRM\Fields\HasMany;
use ParasolCRM\Fields\Money;
use ParasolCRM\Fields\Select;
use ParasolCRM\Fields\Text;
use ParasolCRM\Filters\BetweenFilter;
use ParasolCRM\Filters\Fields\DateFilterField;
use ParasolCRM\Filters\Fields\MultipleSelectFilterField;
use ParasolCRM\Filters\Fields\TextFilterField;
use ParasolCRM\Filters\InFilter;
use ParasolCRM\Filters\LikeFilter;
use ParasolCRM\ResourceScheme;
use ParasolCRM\Statuses\DoughnutStatus;
use ParasolCRM\Statuses\TextStatus;

class PartnerPaymentResource extends ResourceScheme
{
    public static $model = PartnerPayment::class;

    public static $defaultSortBy = 'date_forecasted';

    public static $defaultSortDirection = 'desc';

    public const STATUS_BADGES = [
        'cashed' => 'green',
        'overdue' => 'red',
        'outstanding' => 'yellow',
        'cancelled' => 'gray',
        'postponed' => 'yellow',
        'future payment' => 'purple',
        'forecasted' => 'orange',
    ];

    public const ISSUED_BY_BADGES = [
        'software' => 'blue',
        'loyalty' => 'purple',
    ];

    public const BANKS_BADGES = [
        'rakbank' => 'yellow',
        'mashreq' => 'orange',
    ];

    public function tableQuery(Builder $query)
    {
        return $query->selectRaw('CONCAT(partners.name, " (", partner_contracts.name, ")") as partner_tranche_title')
            ->with('partner', 'files')
            ->leftJoinRelation('partnerTranche.partner');
    }

    public function fields(): array
    {
        return [
            Select::make('status')
                ->options(PartnerPayment::getConstOptions('statuses'))
                ->default(PartnerPayment::STATUSES['outstanding'])
                ->badges(self::STATUS_BADGES)
                ->sortable(),
            Select::make('type')
                ->options(PartnerPayment::getConstOptions('types'))
                ->default(PartnerPayment::TYPES['cheque'])
                ->badges(['default' => 'light'])
                ->sortable(),
            Text::make('cheque_number')
                ->sortable(),
            Money::make('amount')
                ->rules('required')
                ->sortable(),
            Date::make('date_forecasted')
                ->sortable(),
            Date::make('date_actual')
                ->sortable(),
            //            Select::make('issued_by')
            //                ->options(PartnerPayment::getConstOptions('issued_by'))
            //                ->badges(self::ISSUED_BY_BADGES)
            //                ->sortable(),
            //            Select::make('bank')
            //                ->options(PartnerPayment::getConstOptions('banks'))
            //                ->badges(self::BANKS_BADGES)
            //                ->sortable(),
            BelongsTo::make('partner', Partner::class)
                ->titleField('name')
                ->url('/partners/{partner_id}'),
            Text::make('partner_tranche_title', 'Partner Tranche')
                ->sortable('partner_tranche_id')
                ->onlyOnTable()
                ->url('/partner-tranches/{partner_tranche_id}'),
            BelongsTo::make('partnerTranche', PartnerTranche::class)
                ->optionHandler(
                    fn ($query) => $query
                        ->leftJoinRelation('partner')
                        ->selectRaw('CONCAT(partners.name, " (", partner_contracts.name, ")") as title')
                )
                ->onlyOnForm()
            ,
            File::make('tax_invoice')
                ->onlyOnForm(),
            HasMany::make('files', PartnerPaymentFile::class, 'files', 'Attachment files')
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
                'status',
                'partner',
                'partnerTranche',
                'type',
                'cheque_number',
                'amount',
                'date_forecasted',
                'date_actual',
                'status',
                'issued_by',
                'bank',
                'tax_invoice',
            ]),
            Group::make('files')->attach([
                'files',
            ]),
        ];
    }

    public function filters(): array
    {
        return [
            LikeFilter::make(TextFilterField::make(), 'partner_payments.cheque_number', null, 'Cheque Number')->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()->options(PartnerPayment::getConstOptions('statuses')),
                'status',
                'partner_payments.status'
            )->quick(),
            InFilter::make(
                MultipleSelectFilterField::make()->options(PartnerPayment::getConstOptions('issued_by')),
                'issued_by',
                'partner_payments.issued_by',
            ),
            InFilter::make(
                MultipleSelectFilterField::make()->options(PartnerPayment::getConstOptions('banks')),
                'bank',
                'partner_payments.bank',
            ),
            InFilter::make(
                MultipleSelectFilterField::make()->options($this->getPartners()),
                'partner',
                'partner_payments.partner_id'
            )->quick(),

            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'date_forecasted',
                'partner_payments.date_forecasted'
            )
                ->quick(),
            BetweenFilter::make(
                DateFilterField::make(),
                DateFilterField::make(),
                'date_forecasted',
                'partner_payments.date_actual'
            )
                ->quick(),

            BetweenFilter::make(
                new TextFilterField(),
                new TextFilterField(),
                'amount',
                'partner_payments.amount'
            ),
        ];
    }

    public function statuses(): array
    {
        return [
            DoughnutStatus::make('Statuses')
                ->count('partner_payments.status')
                ->labels(PartnerPayment::getConstOptions('statuses'))
                ->colors(self::STATUS_BADGES),
            DoughnutStatus::make('Banks')
                ->count('partner_payments.bank')
                ->labels(PartnerPayment::getConstOptions('banks'))
                ->colors(self::BANKS_BADGES),

            TextStatus::make(
                'Total Cashed Payments',
                fn ($query) => money_formatter(
                    $query->where('partner_payments.status', PartnerPayment::STATUSES['cashed'])->sum('amount')
                )
            ),
            TextStatus::make(
                'Total Outstanding Payments',
                fn ($query) => money_formatter(
                    $query->where('partner_payments.status', PartnerPayment::STATUSES['outstanding'])->sum('amount')
                )
            ),
            TextStatus::make(
                'Total Forecasted Payments',
                fn ($query) => money_formatter(
                    $query->where('partner_payments.status', PartnerPayment::STATUSES['forecasted'])->sum('amount')
                )
            ),
            TextStatus::make(
                'Total Selection Value',
                fn ($query) => money_formatter(
                    $query->sum('amount')
                )
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
