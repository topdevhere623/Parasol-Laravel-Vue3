<?php

namespace App\Providers;

use App\Models\Reports\Lead\CompanyPerformanceLeadReport;
use App\Models\Reports\Lead\TeamPerformanceLeadReport;
use App\ParasolCRMV2\Resources\BackofficeUserResource;
use App\ParasolCRMV2\Resources\BookingResource;
use App\ParasolCRMV2\Resources\MemberResource;
use App\ParasolCRMV2\Resources\PaymentMethodResource;
use ParasolCRMV2\Menu\MenuGroup;
use ParasolCRMV2\Menu\MenuItem;
use ParasolCRMV2\Services\CRM\PrslServiceProvider;

class PrslCustomServiceProvider extends PrslServiceProvider
{
    protected function menu(): array
    {
        if (app()->isProduction()) {
            return [
                MenuItem::make('Leads', 'leads', 'kanban', true),
                MenuGroup::make('Statistics', [
                    MenuItem::make(
                        'Team Performance',
                        'statistics/team-performance',
                        null,
                        null,
                        'index-'.TeamPerformanceLeadReport::class
                    ),
                    MenuItem::make(
                        'Company Performance',
                        'statistics/company-performance',
                        null,
                        null,
                        'index-'.CompanyPerformanceLeadReport::class
                    ),
                ]),
            ];
        }
        return [
            MenuItem::make('Leads', 'leads', 'kanban', true),
            MenuGroup::make('Statistics', [
                MenuItem::make(
                    'Team Performance',
                    'statistics/team-performance',
                    null,
                    null,
                    'index-'.TeamPerformanceLeadReport::class
                ),
                MenuItem::make(
                    'Company Performance',
                    'statistics/company-performance',
                    null,
                    null,
                    'index-'.CompanyPerformanceLeadReport::class
                ),
            ]),

            MenuItem::make('Bookings', BookingResource::class, 'joystick'),
            MenuItem::make('Members', MemberResource::class, 'user'),

            MenuItem::make('Backoffice users', BackofficeUserResource::class, 'abstract-24'),
            MenuItem::make('Payment method', PaymentMethodResource::class, 'abstract-24'),
        ];
    }
}
