<?php

namespace App\Models;

use App\Models\BlogPost\BlogPost;
use App\Models\Club\Checkin;
use App\Models\Club\Club;
use App\Models\Club\ClubTag;
use App\Models\Laratrust\Role;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadDuplicate;
use App\Models\Member\Kid;
use App\Models\Member\Member;
use App\Models\Member\MemberPaymentSchedule;
use App\Models\Member\MembershipDuration;
use App\Models\Member\MembershipProcess;
use App\Models\Member\MembershipRenewal;
use App\Models\Member\MembershipSource;
use App\Models\Member\MembershipType;
use App\Models\Partner\Partner;
use App\Models\Partner\PartnerContact;
use App\Models\Partner\PartnerContract;
use App\Models\Partner\PartnerInventory;
use App\Models\Partner\PartnerPayment;
use App\Models\Partner\PartnerTranche;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentMethod;
use App\Models\Payments\PaymentTransaction;
use App\Models\Payments\PaymentType;
use App\Models\Reports\HSBCClubVisits;
use App\Models\Reports\HSBCClubVisitsSummary;
use App\Models\Reports\HSBCReport;
use App\Models\Reports\ProgramReportMember;
use App\Models\Reports\ReportClubsByMemberSelection;
use App\Models\Reports\ReportClubsByUsage;
use App\Models\Reports\ReportLeadTag;
use App\Models\Reports\ReportMonthlySale;
use App\Models\Reports\ReportTopMember;
use App\Models\WebSite\Faq;
use App\Models\WebSite\FaqCategory;
use App\Models\WebSite\GeneralRule;
use App\Models\WebSite\PackageInfo;
use App\Models\WebSite\Page;

class Menu
{
    private array $counters;

    public function __construct()
    {
        // $this->permissions = Auth::user()->permissions;;
        $this->setCounters();
    }

    public function getAllMenu(): array
    {
        return [
            [
                'title' => 'Dashboard',
                'permission' => 'index-dashboard',
                'link' => '/dashboard',
                'icon' => 'sidenav-icon ion ion-md-speedometer',
                'exact' => true,
            ],
            [
                'title' => 'Bookings',
                'link' => '/bookings',
                'icon' => 'ion ion-md-globe',
                'exact' => true,
                'group' => 'bookings',
                'permission' => 'index-'.Booking::class,
            ],
            [
                'title' => 'Members',
                'link' => '/members',
                'icon' => 'ion ion-md-people',
                'exact' => true,
                'group' => 'members',
                'submenu' => [
                    [
                        'title' => 'All members',
                        'permission' => 'index-'.Member::class,
                        'link' => '/members',
                        'group' => 'members',
                    ],
                    [
                        'title' => 'Kids',
                        'permission' => 'index-'.Kid::class,
                        'link' => '/kids',
                        'group' => 'members',
                    ],
                    [
                        'title' => 'Membership Processes',
                        'link' => '/membership-processes',
                        'group' => 'members',
                        'permission' => 'index-'.MembershipProcess::class,
                    ],
                ],
            ],
            [
                'title' => 'Clubs',
                'link' => '/clubs',
                'icon' => 'ion ion-md-globe',
                'exact' => true,
                'group' => 'clubs',
                'submenu' => [
                    [
                        'title' => 'Clubs',
                        'permission' => 'index-'.Club::class,
                        'link' => '/clubs',
                        'group' => 'clubs',
                    ],
                    [
                        'title' => 'Clubs Checkins',
                        'permission' => 'index-'.Club::class,
                        'link' => '/clubs-checkin',
                        'group' => 'clubs',
                    ],
                    [
                        'title' => 'Clubs sorting',
                        'permission' => 'update-'.Club::class,
                        'link' => '/clubs-sorting',
                        'group' => 'clubs',
                    ],
                    [
                        'title' => 'Club Admins',
                        'permission' => 'index-'.BackofficeUser::class,
                        'link' => '/club-admins',
                        'group' => 'clubs',
                    ],
                ],
            ],
            [
                'title' => 'Partners',
                'link' => '/partners',
                'icon' => 'ion ion-md-globe',
                'exact' => true,
                'group' => 'partners',
                'submenu' => [
                    [
                        'title' => 'Partners',
                        'permission' => 'index-'.Partner::class,
                        'link' => '/partners',
                        'group' => 'partner',
                    ],
                    [
                        'title' => 'Partner Contracts',
                        'permission' => 'update-'.PartnerContract::class,
                        'link' => '/partner-contracts',
                        'group' => 'partner',
                    ],
                    [
                        'title' => 'Partner Tranches',
                        'permission' => 'index-'.PartnerTranche::class,
                        'link' => '/partner-tranches',
                        'group' => 'partner',
                    ],
                    [
                        'title' => 'Partner Payments',
                        'permission' => 'index-'.PartnerPayment::class,
                        'link' => '/partner-payments',
                        'group' => 'clubs',
                    ],
                    [
                        'title' => 'Partner Contacts',
                        'permission' => 'index-'.PartnerContact::class,
                        'link' => '/partner-contacts',
                        'group' => 'clubs',
                    ],
                    [
                        'title' => 'Partner Inventory',
                        'permission' => 'index-'.PartnerInventory::class,
                        'link' => '/partner-inventories',
                        'group' => 'clubs',
                    ],
                ],
            ],
            [
                'title' => 'Offers',
                'link' => '/offers',
                'icon' => 'ion ion-md-globe',
                'exact' => true,
                'group' => 'offers',
                'submenu' => [
                    [
                        'title' => 'Add a new offer',
                        'permission' => 'create-'.Offer::class,
                        'link' => '/offers/create',
                        'group' => 'offers',
                    ],
                    [
                        'title' => 'Offers',
                        'permission' => 'index-'.Offer::class,
                        'link' => '/offers',
                        'group' => 'offers',
                    ],
                ],
            ],
            [
                'title' => 'Check-ins',
                'link' => '/check-ins',
                'icon' => 'ion ion-md-globe',
                'exact' => true,
                'group' => 'check-ins',
                'permission' => 'index-'.Checkin::class,
            ],
            [
                'title' => 'Programs',
                'link' => '/programs',
                'icon' => 'ion ion-ios-paper',
                'exact' => true,
                'group' => 'program',
                'submenu' => [
                    [
                        'title' => 'Programs',
                        'permission' => 'index-'.Program::class,
                        'link' => '/programs',
                        'group' => 'program',
                    ],
                    [
                        'title' => 'Packages',
                        'permission' => 'index-'.Package::class,
                        'link' => '/packages',
                        'group' => 'program',
                    ],
                    [
                        'title' => 'Plans',
                        'permission' => 'index-'.Plan::class,
                        'link' => '/plans',
                        'group' => 'program',
                    ],
                    [
                        'title' => 'Program Admins',
                        'permission' => 'index-'.BackofficeUser::class,
                        'link' => '/program-admins',
                        'group' => 'program',
                    ],
                ],
            ],
            [
                'title' => 'Referrals',
                'link' => '/referrals',
                'icon' => 'ion ion-ios-git-network',
                'exact' => true,
                'group' => 'referrals',
                'submenu' => [
                    [
                        'title' => 'Add a new referral',
                        'permission' => 'create-'.Referral::class,
                        'link' => '/referrals/create',
                        'group' => 'referrals',
                    ],
                    [
                        'title' => 'All referrals',
                        'permission' => 'index-'.Referral::class,
                        'link' => '/referrals',
                        'group' => 'referrals',
                    ],
                ],
            ],
            [
                'title' => 'Coupons',
                'link' => '/coupons',
                'icon' => 'ion ion-ios-barcode',
                'exact' => true,
                'group' => 'coupons',
                'submenu' => [
                    [
                        'title' => 'All coupons',
                        'permission' => 'index-'.Coupon::class,
                        'link' => '/coupons',
                        'group' => 'coupons',
                    ],
                    [
                        'title' => 'Used coupons',
                        'permission' => 'index-'.MemberUsedCoupon::class,
                        'link' => '/coupons-used',
                        'group' => 'coupons',
                    ],
                    [
                        'title' => 'Channels',
                        'link' => '/channels',
                        'icon' => 'ion ion-md-cube',
                        'exact' => true,
                        'permission' => 'index-'.Channel::class,
                    ],
                ],
            ],
            [
                'title' => 'Revenue',
                'link' => '/transactions',
                'icon' => 'ion ion-md-cash',
                'exact' => true,
                'group' => 'revenue',
                'hasAccess' => $this->isAdmin(),
                'submenu' => [
                    [
                        'title' => 'Payments',
                        'link' => '/payments',
                        'group' => 'revenue',
                        'permission' => 'index-'.Payment::class,
                    ],
                    [
                        'title' => 'Transactions',
                        'link' => '/payment-transactions',
                        'group' => 'revenue',
                        'permission' => 'index-'.PaymentTransaction::class,
                    ],
                    [
                        'title' => 'Member scheduled payments',
                        'link' => '/member-scheduled-payments',
                        'group' => 'revenue',
                        'permission' => 'index-'.MemberPaymentSchedule::class,
                    ],
                    [
                        'title' => 'HSBC payments',
                        'link' => '/hsbc-used-cards',
                        'group' => 'revenue',
                        'permission' => 'index-'.HSBCUsedCard::class,
                    ],
                ],
            ],
            [
                'title' => 'Payments',
                'link' => '/payments',
                'group' => 'revenue',
                'icon' => 'ion ion-md-cash',
                'permission' => 'index-'.Payment::class,
                'hasAccess' => !$this->isAdmin() && auth()->user()->hasPermission('index-'.Payment::class),
            ],
            [
                'title' => 'Reports',
                'link' => '/reports',
                'icon' => 'ion ion-md-calendar',
                'exact' => true,
                'group' => 'reports',
                'hasAccess' => $this->isAdmin(),
                'submenu' => [
                    [
                        'title' => 'Membership Renewals',
                        'permission' => 'index-'.MembershipRenewal::class,
                        'link' => '/membership-renewals',
                        'group' => 'reports',
                    ],
                    [
                        'title' => 'HSBC Registrations',
                        'permission' => 'index-'.HSBCReport::class,
                        'link' => '/hsbc-registrations',
                        'group' => 'reports',
                    ],
                    [
                        'title' => 'HSBC Cancellations',
                        'link' => '/hsbc-cancellations',
                        'group' => 'reports',
                        'permission' => 'index-'.HSBCReport::class,
                    ],
                    [
                        'title' => 'HSBC Refunds',
                        'link' => '/hsbc-refunds',
                        'group' => 'reports',
                        'permission' => 'index-'.HSBCReport::class,
                    ],
                    [
                        'title' => 'HSBC Club Visits',
                        'link' => '/hsbc-club-visits',
                        'group' => 'reports',
                        'permission' => 'index-'.HSBCClubVisits::class,
                    ],
                    [
                        'title' => 'HSBC Club Visits Summary',
                        'link' => '/hsbc-club-summary-visits',
                        'group' => 'reports',
                        'permission' => 'index-'.HSBCClubVisitsSummary::class,
                    ],
                    [
                        'title' => 'Program Members',
                        'link' => '/report-program-members',
                        'group' => 'reports',
                        'permission' => 'index-'.ProgramReportMember::class,
                    ],
                    [
                        'title' => 'Clubs by Member selection',
                        'link' => '/report-clubs-by-member-selection',
                        'group' => 'reports',
                        'permission' => 'index-'.ReportClubsByMemberSelection::class,
                    ],
                    [
                        'title' => 'Clubs by Usage',
                        'link' => '/report-clubs-by-usage',
                        'group' => 'reports',
                        'permission' => 'index-'.ReportClubsByUsage::class,
                    ],
                    [
                        'title' => 'Top members',
                        'link' => '/report-top-members',
                        'group' => 'reports',
                        'permission' => 'index-'.ReportTopMember::class,
                    ],
                    [
                        'title' => 'Monthly Sales Report',
                        'link' => '/monthly-sales-reports',
                        'group' => 'reports',
                        'permission' => 'index-'.ReportMonthlySale::class,
                    ],
                    [
                        'title' => 'Lead Tags',
                        'link' => '/report-lead-tags',
                        'group' => 'reports',
                        'permission' => 'index-'.ReportLeadTag::class,
                    ],
                ],
            ],

            [
                'title' => 'Members',
                'link' => '/report-program-members',
                'icon' => 'ion ion-md-people',
                'exact' => false,
                'permission' => 'index-'.ProgramReportMember::class,
                'hasAccess' => !$this->isAdmin() && auth()->user()->hasPermission('index-'.ProgramReportMember::class),
            ],
            [
                'title' => 'Payment Detail',
                'link' => '/hsbc-used-cards',
                'icon' => 'ion ion-md-cash',
                'exact' => false,
                'permission' => 'index-'.HSBCUsedCard::class,
                'hasAccess' => !$this->isAdmin() && auth()->user()->hasPermission('index-'.HSBCUsedCard::class),
            ],
            [
                'title' => 'Registrations',
                'link' => '/hsbc-registrations',
                'icon' => 'ion ion-md-calendar',
                'exact' => false,
                'permission' => 'index-'.HSBCReport::class,
                'hasAccess' => !$this->isAdmin() && auth()->user()->hasPermission('index-'.HSBCReport::class),
            ],
            [
                'title' => 'Cancellations',
                'link' => '/hsbc-cancellations',
                'icon' => 'ion ion-md-calendar',
                'exact' => false,
                'permission' => 'index-'.HSBCReport::class,
                'hasAccess' => !$this->isAdmin() && auth()->user()->hasPermission('index-'.HSBCReport::class),
            ],
            [
                'title' => 'Refunds',
                'link' => '/hsbc-refunds',
                'icon' => 'ion ion-md-calendar',
                'exact' => false,
                'permission' => 'index-'.HSBCReport::class,
                'hasAccess' => !$this->isAdmin() && auth()->user()->hasPermission('index-'.HSBCReport::class),
            ],
            [
                'title' => 'Club Visits',
                'link' => '/hsbc-club-visits',
                'icon' => 'ion ion-md-calendar',
                'exact' => true,
                'permission' => 'index-'.HSBCClubVisits::class,
                'hasAccess' => !$this->isAdmin() && auth()->user()->hasPermission('index-'.HSBCClubVisits::class),
            ],
            [
                'title' => 'Club Summary',
                'link' => '/hsbc-club-summary-visits',
                'icon' => 'ion ion-md-calendar',
                'exact' => true,
                'permission' => 'index-'.HSBCClubVisitsSummary::class,
                'hasAccess' => !$this->isAdmin() && auth()->user()->hasPermission(
                    'index-'.HSBCClubVisitsSummary::class
                ),
            ],
            [
                'title' => 'Sales Quotes',
                'link' => '/sales-quotes',
                'icon' => 'ion ion-md-paper',
                'exact' => true,
                'group' => 'quotes',
                'permission' => 'index-'.SalesQuote::class,
            ],
            [
                'title' => 'Web Form Requests',
                'link' => '/web-form-requests',
                'icon' => 'ion ion-md-mail',
                'exact' => true,
                'group' => 'web-form-requests',
                'permission' => 'index-'.WebFormRequest::class,
            ],
            [
                // Show in menu
                'title' => 'Website content',
                // url
                'link' => '/web-site-settings',
                // Menu show icon
                'icon' => 'sidenav-icon ion ion-ios-cog',
                'exact' => true,
                'group' => 'web-site-setting',
                'submenu' => [
                    [
                        'title' => 'FAQ categories',
                        'permission' => 'index-'.FaqCategory::class,
                        'link' => '/faq-categories',
                        'group' => 'web-site-setting',
                    ],
                    [
                        'title' => 'FAQs',
                        'permission' => 'index-'.Faq::class,
                        'link' => '/faqs',
                        'group' => 'web-site-setting',
                    ],
                    [
                        'title' => 'General rules',
                        'permission' => 'index-'.GeneralRule::class,
                        'link' => '/general-rules',
                        'group' => 'web-site-setting',
                    ],
                    [
                        'title' => 'Package infos',
                        'permission' => 'index-'.PackageInfo::class,
                        'link' => '/package-infos',
                        'group' => 'web-site-setting',
                    ],
                    [
                        'title' => 'Pages',
                        'permission' => 'index-'.Page::class,
                        'link' => '/pages',
                        'group' => 'web-site-setting',
                    ],
                    [
                        'title' => 'Our Partners',
                        'permission' => 'index-'.OurPartner::class,
                        'link' => '/our-partners',
                        'group' => 'web-site-setting',
                    ],
                    [
                        'title' => 'Documents',
                        'permission' => 'index-'.Document::class,
                        'link' => '/documents',
                        'group' => 'web-site-setting',
                    ],
                    [
                        'title' => 'Testimonials',
                        'permission' => 'index-'.Testimonial::class,
                        'link' => '/testimonials',
                        'group' => 'web-site-setting',
                    ],
                    [
                        'title' => 'Blog posts',
                        'permission' => 'index-'.BlogPost::class,
                        'link' => '/blog-posts',
                        'group' => 'web-site-setting',
                    ],
                ],
            ],
            [
                // Show in menu
                'title' => 'Settings',
                // url
                'link' => '/settings',
                // Menu show icon
                'icon' => 'sidenav-icon ion ion-ios-settings',
                'exact' => true,
                'group' => 'settings',
                'submenu' => [
                    [
                        'title' => 'Payment Methods',
                        'permission' => 'index-'.PaymentMethod::class,
                        'link' => '/payment-methods',
                        'group' => 'settings',
                    ],
                    [
                        'title' => 'Payment Types',
                        'permission' => 'index-'.PaymentType::class,
                        'link' => '/payment-types',
                        'group' => 'settings',
                    ],
                    [
                        'title' => 'Membership types',
                        'permission' => 'index-'.MembershipType::class,
                        'link' => '/membership-types',
                        'group' => 'settings',
                    ],
                    [
                        'title' => 'Membership sources',
                        'permission' => 'index-'.MembershipSource::class,
                        'link' => '/membership-sources',
                        'group' => 'settings',
                    ],
                    [
                        'title' => 'Membership source sorting',
                        'permission' => 'index-'.MembershipSource::class,
                        'link' => '/membership-source-sorting',
                        'group' => 'settings',
                    ],
                    [
                        'title' => 'Membership durations',
                        'permission' => 'index-'.MembershipDuration::class,
                        'link' => '/membership-durations',
                        'group' => 'settings',
                    ],
                    [
                        'title' => 'Corporates',
                        'permission' => 'index-'.Corporate::class,
                        'link' => '/corporates',
                        'group' => 'settings',
                    ],
                    [
                        'title' => 'Club tags',
                        'permission' => 'index-'.ClubTag::class,
                        'link' => '/club-tags',
                        'group' => 'settings',
                    ],
                    [
                        'title' => 'Offer types',
                        'permission' => 'index-'.OfferType::class,
                        'link' => '/offer-types',
                        'group' => 'settings',
                    ],
                    [
                        'title' => 'HSBC Bins',
                        'permission' => 'index-'.HSBCBin::class,
                        'link' => '/hsbc-bins',
                        'group' => 'settings',
                    ],
                    [
                        'title' => 'Settings',
                        'permission' => 'view-'.Setting::class,
                        'link' => '/settings',
                        'group' => 'settings',
                    ],
                ],
            ],
            [
                // Show in menu
                'title' => 'Admins',
                // url
                'link' => '/admins',
                // Menu show icon
                'icon' => 'ion ion-md-person',
                'exact' => true,
                'group' => 'admin',
                'submenu' => [
                    [
                        // Submenu show menu title
                        'title' => 'Add a new admin',
                        // MENU PERMISION KEY
                        'permission' => 'create-'.BackofficeUser::class,
                        // url
                        'link' => '/admins/create',
                        'group' => 'admin',
                    ],
                    [
                        'title' => 'Full list',
                        'permission' => 'index-'.BackofficeUser::class,
                        'link' => '/admins',
                        'group' => 'admin',
                    ],
                    [
                        'title' => 'Roles',
                        'permission' => 'index-'.Role::class,
                        'link' => '/roles',
                        'group' => 'admin',
                    ],
                ],
            ],
            [
                // Show in menu
                'title' => 'Leads',
                // url
                'link' => '/leads',
                // Menu show icon
                'icon' => 'ion ion-md-people',
                'exact' => true,
                'group' => 'leads',
                'submenu' => [
                    [
                        'title' => 'Leads',
                        'link' => '/leads',
                        'permission' => 'index-'.Lead::class,
                    ],
                    [
                        'title' => 'Lead Duplicates',
                        'link' => '/lead-duplicates',
                        'permission' => 'index-'.LeadDuplicate::class,
                    ],
                ],
            ],
            [
                'title' => 'Newsletter Subscriptions',
                'link' => '/news-subscriptions',
                'icon' => 'ion ion-md-mail',
                'exact' => true,
                'permission' => 'index-'.NewsSubscription::class,
            ],
        ];
    }

    /**
     * @return array
     */
    private function counterList(): array
    {
        return [
        ];
    }

    /**
     * @param $key
     *
     * @return array
     */
    private function getMenuItemByKey($key): array
    {
        $menu = $this->getAllMenu()[$key];
        if (isset($menu['submenu'])) {
            unset($menu['submenu']);
        }
        if (isset($menu['counter'])) {
            $menu['counter'] = $this->getCounterValue($menu['counter']);
        }

        return $menu;
    }

    /**
     * @return array
     */
    public function getAccessMenu(): array
    {
        $data = [];
        foreach ($this->getAllMenu() as $key => $menu) {
            if ($this->userCanAccessMenu($menu)) {
                $data[$key] = $this->getMenuItemByKey($key);
                if (isset($menu['submenu'])) {
                    foreach ($menu['submenu'] as $subKey => $subMenu) {
                        $canCurrentSubMenu = $this->userCanAccessMenu($subMenu);
                        if ($canCurrentSubMenu) {
                            if (isset($subMenu['counter'])) {
                                $subMenu['counter'] = $this->getCounterValue($subMenu['counter']);
                            }
                            $data[$key]['submenu'][$subKey] = $subMenu;
                        }
                    }
                }
            }
            if (!isset($data[$key]['permission']) && !isset($data[$key]['submenu'])) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * @param array $menu
     *
     * @return bool
     */
    private function userCanAccessMenu(array $menu): bool
    {
        if (isset($menu['hasAccess'])) {
            return is_callable($menu['hasAccess']) ? call_user_func($menu['hasAccess']) : $menu['hasAccess'];
        } elseif (isset($menu['permission'])) {
            return auth()->user()->hasPermission($menu['permission']);
            // Prsl::checkGatePolicy('index', $this->resource->model)
            // return (array_search($menu['permission'], $this->permissions, true) !== false);
        }

        return true;
    }

    /**
     * @param string $counter
     *
     * @return int
     */
    private function getCounterValue(string $counter): int
    {
        return $this->counters[$counter] ?? 0;
    }

    /**
     * @return void
     */
    private function setCounters(): void
    {
        foreach ($this->counterList() as $key => $counter) {
            $this->counters[$key] = call_user_func($counter);
        }
    }

    private function isAdmin(): bool
    {
        return \Auth::guard('backoffice_user')->user()->isAdmin();
    }

    private function isPartner(): bool
    {
        return \Auth::guard('backoffice_user')->user()->hasRole('partner-admin');
    }
}
