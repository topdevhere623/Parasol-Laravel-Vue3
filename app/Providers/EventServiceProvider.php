<?php

namespace App\Providers;

use App\Listeners\LoginLogListener;
use App\Listeners\SubjectPrefixSendingMessage;
use App\Models\BlogPost\BlogPost;
use App\Models\Booking;
use App\Models\Club\Checkin;
use App\Models\Club\Club;
use App\Models\Club\ClubTag;
use App\Models\Coupon;
use App\Models\Lead\CrmComment;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadDuplicate;
use App\Models\Member\Kid;
use App\Models\Member\MemberClubPivot;
use App\Models\Member\MemberPasskit;
use App\Models\Member\MembershipProcess;
use App\Models\Member\MembershipRenewal;
use App\Models\Offer;
use App\Models\Partner\PartnerContract;
use App\Models\Partner\PartnerTranche;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentTransaction;
use App\Models\Plan;
use App\Models\Program;
use App\Models\ProgramPlanReferralPivot;
use App\Models\Referral;
use App\Models\SalesQuote;
use App\Observers\BlogPostObserver;
use App\Observers\BookingObserver;
use App\Observers\CheckinObserver;
use App\Observers\ClubObserver;
use App\Observers\ClubTagObserver;
use App\Observers\CouponObserver;
use App\Observers\KidObserver;
use App\Observers\Lead\CrmCommentObserver;
use App\Observers\Lead\LeadDuplicateObserver;
use App\Observers\Lead\LeadObserver;
use App\Observers\MemberClubPivotObserver;
use App\Observers\MemberPasskitObserver;
use App\Observers\MembershipProcessObserver;
use App\Observers\MembershipRenewalObserver;
use App\Observers\OfferObserver;
use App\Observers\PartnerContractObserver;
use App\Observers\PartnerTrancheObserver;
use App\Observers\PaymentObserver;
use App\Observers\PaymentTransactionObserver;
use App\Observers\PlanObserver;
use App\Observers\ProgramObserver;
use App\Observers\ProgramPlanReferralPivotObserver;
use App\Observers\ReferralObserver;
use App\Observers\SalesQuoteObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use Laravel\Passport\Events\AccessTokenCreated;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        MessageSending::class => [
            SubjectPrefixSendingMessage::class,
        ],
        AccessTokenCreated::class => [
            LoginLogListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        self::registerObservers();
    }

    /**
     * Register model observers
     *
     * @return void
     */
    public function registerObservers()
    {
        BlogPost::observe(BlogPostObserver::class);
        Booking::observe(BookingObserver::class);
        Checkin::observe(CheckinObserver::class);
        Club::observe(ClubObserver::class);
        ClubTag::observe(ClubTagObserver::class);
        CrmComment::observe(CrmCommentObserver::class);
        Kid::observe(KidObserver::class);
        MemberClubPivot::observe(MemberClubPivotObserver::class);
        MemberPasskit::observe(MemberPasskitObserver::class);
        Offer::observe(OfferObserver::class);
        Payment::observe(PaymentObserver::class);
        PaymentTransaction::observe(PaymentTransactionObserver::class);
        MembershipProcess::observe(MembershipProcessObserver::class);
        MembershipRenewal::observe(MembershipRenewalObserver::class);
        PartnerContract::observe(PartnerContractObserver::class);
        PartnerTranche::observe(PartnerTrancheObserver::class);
        Program::observe(ProgramObserver::class);
        ProgramPlanReferralPivot::observe(ProgramPlanReferralPivotObserver::class);
        Referral::observe(ReferralObserver::class);
        Coupon::observe(CouponObserver::class);
        SalesQuote::observe(SalesQuoteObserver::class);
        Lead::observe(LeadObserver::class);
        LeadDuplicate::observe(LeadDuplicateObserver::class);
        Plan::observe(PlanObserver::class);
    }
}
