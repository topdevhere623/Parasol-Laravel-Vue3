<?php

namespace Database\Seeders;

use App\Models\BackofficeUser;
use App\Models\Booking;
use App\Models\Club\Checkin;
use App\Models\Club\Club;
use App\Models\Club\ClubTag;
use App\Models\Corporate;
use App\Models\Coupon;
use App\Models\Document;
use App\Models\HSBCBin;
use App\Models\HSBCUsedCard;
use App\Models\Laratrust\Permission;
use App\Models\Laratrust\Role;
use App\Models\Laratrust\Team;
use App\Models\Member\Junior;
use App\Models\Member\Kid;
use App\Models\Member\Member;
use App\Models\Member\MemberPrimary;
use App\Models\Member\MembershipDuration;
use App\Models\Member\MembershipProcess;
use App\Models\Member\MembershipSource;
use App\Models\Member\MembershipType;
use App\Models\MemberUsedCoupon;
use App\Models\Offer;
use App\Models\OfferType;
use App\Models\OurPartner;
use App\Models\Package;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentMethod;
use App\Models\Payments\PaymentType;
use App\Models\Plan;
use App\Models\Program;
use App\Models\Referral;
use App\Models\Reports\HSBCReport;
use App\Models\Testimonial;
use App\Models\WebFormRequest;
use App\Models\WebSite\Faq;
use App\Models\WebSite\FaqCategory;
use App\Models\WebSite\GeneralRule;
use App\Models\WebSite\PackageInfo;
use App\Models\WebSite\Page;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::query()->delete();

        $permissionGroups = [
            [
                'model' => BackofficeUser::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Admins',
            ],
            [
                'model' => Member::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Members',
            ],
            [
                'model' => MemberPrimary::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Primary Members',
            ],
            [
                'model' => \App\Models\Member\Partner::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Member Partners',
            ],
            [
                'model' => Junior::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Juniors',
            ],
            [
                'model' => Kid::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Kids',
            ],
            [
                'model' => MembershipProcess::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Membership Process',
            ],
            [
                'model' => Club::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Clubs',
            ],
            [
                'model' => Program::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Programs',
            ],
            [
                'model' => Package::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Packages',
            ],
            [
                'model' => Plan::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Plans',
            ],
            [
                'model' => Offer::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Offers',
            ],
            [
                'model' => Referral::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Referrals',
            ],
            [
                'model' => Coupon::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Coupons',
            ],
            [
                'model' => MemberUsedCoupon::class,
                'routes' => ['index', 'view'],
                'display_name' => 'Coupons used',
            ],
            [
                'model' => OurPartner::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Our partners',
            ],
            [
                'model' => Document::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Documents',
            ],
            [
                'model' => Testimonial::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Testimonials',
            ],
            [
                'model' => FaqCategory::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Faq categories',
            ],
            [
                'model' => Faq::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Faqs',
            ],
            [
                'model' => GeneralRule::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'General rules',
            ],
            [
                'model' => PackageInfo::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Package infos',
            ],
            [
                'model' => Page::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Pages',
            ],
            [
                'model' => PaymentMethod::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Payments',
            ],
            [
                'model' => MembershipType::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Membership types',
            ],
            [
                'model' => MembershipSource::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Membership sources',
            ],
            [
                'model' => MembershipDuration::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Membership durations',
            ],
            [
                'model' => Corporate::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Corporates',
            ],
            [
                'model' => ClubTag::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Club tags',
            ],
            [
                'model' => Checkin::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Check-ins',
            ],
            [
                'model' => OfferType::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Offer types',
            ],
            [
                'model' => HSBCBin::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'HSBC Bins',
            ],
            [
                'model' => WebFormRequest::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Web Form Request',
            ],
            [
                'model' => Payment::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Payments',
            ],
            [
                'model' => HSBCUsedCard::class,
                'routes' => ['index', 'log', 'delete', 'view'],
                'display_name' => 'HSBC Used Cards',
            ],
            [
                'model' => HSBCReport::class,
                'routes' => ['index'],
                'display_name' => 'HSBC Report',
            ],
            [
                'model' => PaymentType::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Payment Types',
            ],
            [
                'model' => Booking::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Bookings',
            ],
        ];

        $supervisorPermissions = [
            [
                'model' => Role::class,
                'routes' => ['index', 'create', 'view', 'update', 'delete', 'log'],
                'display_name' => 'Roles',
            ],
            [
                'model' => Team::class,
                'routes' => ['index', 'view'],
                'display_name' => 'Teams',
            ],
        ];

        // Supervisor permissions
        $permissionsAttach = [];

        foreach ($supervisorPermissions as $group) {
            foreach ($group['routes'] as $route) {
                $permissionsAttach[] = $this->updateOrCreate($route, $group);
            }
        }

        $role = Role::where('name', 'supervisor')->first();

        // $role->detachPermissions($permissionsAttach);
        $role->attachPermissions($permissionsAttach);

        // Other permissions
        $permissionsAttach = [];
        foreach ($permissionGroups as $group) {
            foreach ($group['routes'] as $route) {
                $permissionsAttach[] = $this->updateOrCreate($route, $group);
            }
        }

        $role->attachPermissions($permissionsAttach);

        $role = Role::where('name', 'manager')->first();

        // $role->detachPermissions($permissionsAttach);
        $role->attachPermissions($permissionsAttach);

        $clubManagerPermissions = [
            [
                'model' => Checkin::class,
                'routes' => ['index', 'create'/* 'view' */],
                'display_name' => 'Check-ins',
            ],
        ];

        $role = Role::where('name', 'club_manager')->first();

        // Other permissions
        $permissionsAttach = [];
        foreach ($clubManagerPermissions as $group) {
            foreach ($group['routes'] as $route) {
                $permissionsAttach[] = $this->updateOrCreate($route, $group);
            }
        }

        // $role->detachPermissions($permissionsAttach);
        $role->attachPermissions($permissionsAttach);
    }

    public function updateOrCreate($route, $group)
    {
        return Permission::updateOrCreate([
            'name' => $route.'-'.$group['model'],
            'display_name' => $group['display_name'],
        ]);
    }
}
