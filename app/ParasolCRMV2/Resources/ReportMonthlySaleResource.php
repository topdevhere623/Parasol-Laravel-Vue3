<?php

namespace App\ParasolCRMV2\Resources;

use App\Enum\Booking\StepEnum;
use App\Models\Booking;
use App\Models\Lead\Lead;
use App\Models\Reports\ReportMonthlySale;
use App\ParasolCRMV2\Filters\ReportMonthlySaleDateFilter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Fields\Money;
use ParasolCRMV2\Fields\Number;
use ParasolCRMV2\Fields\Text;
use ParasolCRMV2\Filters\Fields\DateFilterField;
use ParasolCRMV2\ResourceScheme;
use ParasolCRMV2\Services\CRM\Facades\PrslV2;
use ParasolCRMV2\Statuses\DoughnutStatus;

class ReportMonthlySaleResource extends ResourceScheme
{
    public static $model = ReportMonthlySale::class;
    public static $defaultSortBy = 'backoffice_users.first_name';
    public static $defaultSortDirection = 'asc';

    private array $leadTags = [
        2 => 'google',
        8 => 'instagram',
        9 => 'facebook',
        4 => 'tiktok',
        10 => 'website',
    ];
    private array $salesTags;

    public function __construct()
    {
        $this->salesTags = [1 => 'referral', 3 => 'renewal', 15 => 'outsorced', 14 => 'hsbc'] + $this->leadTags;
    }

    public function query(Builder $query)
    {
        $dateFilter = PrslV2::getRequestFilters()['date'] ?? null;

        $salesTagsSub = Booking::selectRaw(
            'bookings.id, bookings.step, bookings.total_price, bookings.lead_id, bookings.last_step_changed_at, bookings.units'
        )
            ->leftJoin('leads', 'leads.id', '=', 'bookings.lead_id')
            ->join('lead_lead_tag', 'lead_lead_tag.lead_id', '=', 'leads.id')
            ->where('bookings.step', StepEnum::Completed)
            ->groupBy('bookings.id');

        $leadsSub = Lead::selectRaw('leads.id, leads.created_at')
            ->join('lead_lead_tag', 'lead_lead_tag.lead_id', '=', 'leads.id')
            ->groupBy('leads.id');

        if (!empty($dateFilter['from'])) {
            $from = Carbon::parse($dateFilter['from'])->startOfDay();
            $salesTagsSub->where('bookings.last_step_changed_at', '>=', $from);
            $leadsSub->where('leads.created_at', '>=', $from);
        }

        if (!empty($dateFilter['to'])) {
            $to = Carbon::parse($dateFilter['to'])->endOfDay();
            $salesTagsSub->where('bookings.last_step_changed_at', '<=', $to);
            $leadsSub->where('leads.created_at', '<=', $to);
        }

        foreach ($this->leadTags as $tagId => $tag) {
            $leadsSub->selectRaw(
                "IF(lead_lead_tag.lead_tag_id = {$tagId}, 1, 0) as {$tag}"
            );

            $query->selectRaw(
                "IFNULL(SUM(leads_with_tags.{$tag}), 0) as {$tag}_l"
            );
            $query->selectRaw(
                "(IFNULL(SUM(bookings.{$tag}), 0) / IFNULL(SUM(leads_with_tags.{$tag}), 0) * 100) as {$tag}_c"
            );
        }

        foreach ($this->salesTags as $tagId => $tag) {
            $salesTagsSub->addSelect([
                $tag => fn ($query) => $query->from('lead_lead_tag as lead_lead_tag_unexplained')
                    ->whereRaw('lead_lead_tag_unexplained.lead_id = leads.id')
                    ->where('lead_lead_tag_unexplained.lead_tag_id', $tagId)
                    ->select(\DB::raw('IF(COUNT(*), 1 ,0)')),
            ]);

            //            $salesTagsSub->selectRaw(
            //                "IF(lead_lead_tag.lead_tag_id = {$tagId}, 1, 0) as {$tag}"
            //            );
            $query->selectRaw(
                "IFNULL(SUM(bookings.{$tag}), 0) as {$tag}_s"
            );
        }

        $salesTagsSub->addSelect([
            'unexplained' => fn ($query) => $query->from('lead_lead_tag as lead_lead_tag_unexplained')
                ->whereRaw('lead_lead_tag_unexplained.lead_id = leads.id')
                ->whereIn('lead_lead_tag_unexplained.lead_tag_id', array_keys($this->salesTags))
                ->select(\DB::raw('IF(COUNT(*), 0 ,1)')),
        ]);

        $query->selectRaw(
            'IFNULL(SUM(bookings.unexplained), 0) as unexplained_s'
        );

        $query->selectRaw(
            'SUM(bookings.total_price) as revenues, IFNULL(SUM(bookings.units), 0) as units, COUNT(bookings.id) as bookings_count'
        )
            ->leftJoin('role_user', 'role_user.user_id', '=', 'backoffice_users.id')
            ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('leads', 'backoffice_users.id', '=', 'leads.backoffice_user_id')
            ->joinSub($salesTagsSub, 'bookings', 'bookings.lead_id', '=', 'leads.id', 'left')
            ->joinSub($leadsSub, 'leads_with_tags', 'leads_with_tags.id', '=', 'leads.id', 'left')
            ->where('roles.name', 'sales');
    }

    public function fields(): array
    {
        return [
            Text::make('full_name')
                ->column('first_name')
                ->sortable()
                ->onlyOnTable()
                ->url('/admins/{id}')
                ->displayHandler(fn ($record) => $record->full_name),
            Number::make('units')
                ->sortable('units'),
            Money::make('revenues')
                ->sortable('revenues'),
            Number::make('bookings_count', 'Transactions')
                ->sortable('bookings_count'),
            Number::make('google_l', 'Google (L)')
                ->sortable('google_l'),
            Number::make('instagram_l', 'Instagram (L)')
                ->sortable('instagram_l'),
            Number::make('facebook_l', 'Facebook (L)')
                ->sortable('facebook_l'),
            Number::make('tiktok_l', 'TikTok (L)')
                ->sortable('tiktok_l'),
            Number::make('website_l', 'Website (L)')
                ->sortable('website_l'),
            Number::make('google_s', 'Google (S)')
                ->url($this->salesUrl('google'))
                ->sortable('google_s'),
            Number::make('instagram_s', 'Instagram (S)')
                ->url($this->salesUrl('instagram'))
                ->sortable('instagram_s'),
            Number::make('facebook_s', 'Facebook (S)')
                ->url($this->salesUrl('facebook'))
                ->sortable('facebook_s'),
            Number::make('tiktok_s', 'TikTok (S)')
                ->url($this->salesUrl('tiktok'))
                ->sortable('tiktok_s'),
            Number::make('website_s', 'Website (S)')
                ->url($this->salesUrl('website'))
                ->sortable('website_s'),
            Number::make('referral_s', 'Referral (S)')
                ->url($this->salesUrl('referral'))
                ->sortable('referral_s'),
            Number::make('outsorced_s', 'Outsorced (S)')
                ->url($this->salesUrl('outsorced'))
                ->sortable('outsorced_s'),
            Number::make('renewal_s', 'Renewal (S)')
                ->url($this->salesUrl('renewal'))
                ->sortable('renewal_s'),
            Number::make('hsbc_s', 'HSBC (S)')
                ->url($this->salesUrl('hsbc'))
                ->sortable('hsbc_s'),
            Number::make('unexplained_s', 'Unexplained (S)')
                ->url($this->salesUrl())
                ->sortable('unexplained_s'),
            Text::make('google_c', 'Google (C)')
                ->displayHandler(fn ($record) => round($record->google_c ?? 0).'%')
                ->sortable('google_c'),
            Text::make('instagram_c', 'Instagram (C)')
                ->displayHandler(fn ($record) => round($record->instagram_c ?? 0).'%')
                ->sortable('instagram_c'),
            Text::make('facebook_c', 'Facebook (C)')
                ->displayHandler(fn ($record) => round($record->facebook_c ?? 0).'%')
                ->sortable('facebook_c'),
            Text::make('tiktok_c', 'TikTok (C)')
                ->displayHandler(fn ($record) => round($record->tiktok_c ?? 0).'%')
                ->sortable('tiktok_c'),
            Text::make('website_c', 'Website (C)')
                ->displayHandler(fn ($record) => round($record->website_c ?? 0).'%')
                ->sortable('website_c'),
        ];
    }

    public function statuses(): array
    {
        return [
            //            DoughnutStatus::make('Number of visits by program')
            //                ->count('members.program_id', 'checkins.id')
            //                ->labels($this->getPropsList('name', 'Unknown')),
        ];
    }

    public function filters(): array
    {
        return [
            ReportMonthlySaleDateFilter::make(
                DateFilterField::make()->default(today()->startOfMonth()),
                DateFilterField::make(),
                'date',
                ''
            )->quick(),
        ];
    }

    private function salesUrl($tagName = null): \Closure
    {
        $dateFilter = PrslV2::getRequestFilters()['date'] ?? [];

        $tagList = array_flip($this->salesTags);

        // strval convertions are needed because of the json_encode and multiselect doesn't work with integers
        $tags = $tagName ? [strval($tagList[$tagName])] : array_map('strval', array_values($tagList));

        $tagsFilter = $tagName ? 'include_tags' : 'exclude_tags';
        return fn ($record) => \URL::backoffice(
            'bookings',
            [
                'filters' => json_encode(
                    [
                        'sales_person' => [strval($record->id)],
                        'step' => StepEnum::Completed,
                        $tagsFilter => $tags,
                        'last_step_changed_at' => $dateFilter,
                    ],
                    JSON_FORCE_OBJECT
                ),
            ]
        );
    }

}
