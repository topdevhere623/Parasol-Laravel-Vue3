<?php

use App\Models\Area;
use App\Models\Booking;
use App\Models\Channel;
use App\Models\City;
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
use App\Models\Member\MemberPaymentSchedule;
use App\Models\Member\MemberPrimary;
use App\Models\Member\MembershipDuration;
use App\Models\Member\MembershipProcess;
use App\Models\Member\MembershipRenewal;
use App\Models\Member\MembershipSource;
use App\Models\Member\MembershipType;
use App\Models\Member\Partner as MemberPartner;
use App\Models\MemberUsedCoupon;
use App\Models\Offer;
use App\Models\OfferType;
use App\Models\OurPartner;
use App\Models\Package;
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
use App\Models\Plan;
use App\Models\Program;
use App\Models\Referral;
use App\Models\Reports\HSBCClubVisits;
use App\Models\Reports\HSBCClubVisitsSummary;
use App\Models\Reports\HSBCReport;
use App\Models\SalesQuote;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\WebFormRequest;
use App\Models\WebSite\Faq;
use App\Models\WebSite\FaqCategory;
use App\Models\WebSite\GeneralRule;
use App\Models\WebSite\PackageInfo;
use App\Models\WebSite\Page;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up()
    {
        foreach (
            [
                'Areas' => Area::class,
                'Admins' => App\Models\BackofficeUser::class,
                'Bookings' => Booking::class,
                'Channels' => Channel::class,
                'Cities' => City::class,
                'Clubs' => Club::class,
                'Club Tags' => ClubTag::class,
                'Checkins' => Checkin::class,
                'Coupons' => Coupon::class,
                'Corporates' => Corporate::class,
                'Documents' => Document::class,
                'Faq Categories' => FaqCategory::class,
                'Faqs' => Faq::class,
                'General Rules' => GeneralRule::class,
                'HSBC Bins' => HSBCBin::class,
                'HSBC Club Visitss' => HSBCClubVisits::class,
                'HSBC Club Visits Summaries' => HSBCClubVisitsSummary::class,
                'HSBC Reports' => HSBCReport::class,
                'HSBC Used Cards' => HSBCUsedCard::class,
                'Juniors' => Junior::class,
                'Kids' => Kid::class,
                'Member Partners' => MemberPartner::class,
                'Member Payment Schedule' => MemberPaymentSchedule::class,
                'Member Primarys' => MemberPrimary::class,
                'Member Used Coupons' => MemberUsedCoupon::class,
                'Members' => Member::class,
                'Membership Durations' => MembershipDuration::class,
                'Membership Processes' => MembershipProcess::class,
                'Membership Renewals' => MembershipRenewal::class,
                'Membership Sources' => MembershipSource::class,
                'Membership Types' => MembershipType::class,
                'Offer Types' => OfferType::class,
                'Offers' => Offer::class,
                'Our Partners' => OurPartner::class,
                'Package Infos' => PackageInfo::class,
                'Packages' => Package::class,
                'Pages' => Page::class,
                'Partners' => Partner::class,
                'Partner Contacts' => PartnerContact::class,
                'Partner Contracts' => PartnerContract::class,
                'Partner Inventorys' => PartnerInventory::class,
                'Partner Payments' => PartnerPayment::class,
                'Partner Tranches' => PartnerTranche::class,
                'Payment Transactions' => PaymentTransaction::class,
                'Payment Types' => PaymentType::class,
                'Payment Methods' => PaymentMethod::class,
                'Payments' => Payment::class,
                'Plans' => Plan::class,
                'Programs' => Program::class,
                'Quotes' => SalesQuote::class,
                'Referrals' => Referral::class,
                'Roles' => Role::class,
                'Settings' => Setting::class,
                'Teams' => Team::class,
                'Testimonials' => Testimonial::class,
                'WebFormRequests' => WebFormRequest::class,
            ] as $displayName => $model
        ) {
            $permission = Permission::updateOrCreate([
                'name' => "export-{$model}",
                'display_name' => $displayName,
            ]);
            Role::whereName('supervisor')->first()->attachPermission($permission);
            Role::whereName('manager')->first()->attachPermission($permission);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
