<?php

use App\Http\Controllers\Web\Booking\BookingCompleteController;
use App\Http\Controllers\Web\Booking\BookingStepOneController;
use App\Http\Controllers\Web\Booking\BookingStepThreeController;
use App\Http\Controllers\Web\Booking\BookingStepTwoController;
use App\Http\Controllers\Web\Booking\Payment\BookingPaymentAmazonController;
use App\Http\Controllers\Web\Booking\Payment\BookingPaymentPaytabsController;
use App\Http\Controllers\Web\Booking\Payment\BookingPaymentTabbyController;
use App\Http\Controllers\Web\LegacyUrlRedirectController;
use App\Http\Controllers\Web\LocationController;
use App\Http\Controllers\Web\MemberPaymentScheduleController;
use App\Http\Controllers\Web\PaymentMamoController;
use App\Http\Controllers\Web\SignInController;
use App\Http\Controllers\Web\WebFormRequestController;
use App\Http\Controllers\Web\ZohoController;
use App\Http\Middleware\BookingPaymentProcessingMiddleware;
use App\Http\Middleware\SetThemeBySubdomain;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
Route::group(['namespace' => '\App\Http\Controllers\Web'], function () {
    // WEB SITE PART

    // HomeController
    Route::get('', 'HomeController@index')->name('home');
    Route::get('hsbc', 'HomeController@hsbc')->name('hsbc-home');
    Route::get('blog', 'BlogController@index')->name('blog-posts');
    Route::get('blog/{blog}', 'BlogController@show')->name('blog-post');
    Route::get('links', 'PageController@links')->name('links');

    Route::get('sign-in', [SignInController::class, 'signIn'])->name('sign-in');

    Route::group(['middleware' => SetThemeBySubdomain::class], function () {
        // web-form-request Controller
        Route::get('faq', 'FaqController@faq')->name('faq.index');
        // Page Controller
        Route::get('get/countries', 'PageController@getCountries');
        // Club Controller
        Route::prefix('clubs')
            ->group(function () {
                Route::get('', 'ClubController@index')->name('website.clubs.index');
                Route::get('{slug}', 'ClubController@show')->name('website.clubs.show');
            });
        // Page Controller
        Route::prefix('page')
            ->group(function () {
                Route::get('instalments-payments', 'PageController@instalmentsPayments')
                    ->name('page.instalments-payments');
                Route::view('tabby', 'page.tabby')->name('page.tabby');
                Route::get('entertainer-hsbc-soleil-faqs', 'PageController@hsbcFaq')
                    ->name('page.hsbc-faq');
                Route::get('{slug}', 'PageController@page')
                    ->name('page.show');
            });
    });

    Route::get('data/DetailedClubInfoGeneric.pdf', 'FileController@getDetailedClubInfoGeneric')
        ->name('detailed_club_info_doc');
    Route::get('uploads/map/dubai_map.png', 'FileController@getMap');
    Route::get('uploads/documents/{filename}', 'FileController@getFile');

    Route::get('uploads/{path}', [LegacyUrlRedirectController::class, '__invoke'])
        ->where('path', '(.*)');

    // web-form-request Controller
    Route::post('web-form-request/club-information', 'WebFormRequestController@clubInformation');
    Route::post('web-form-request/corporate-pricing', 'WebFormRequestController@corporatePricing');
    Route::post('web-form-request/suggestion', 'WebFormRequestController@suggestion');
    Route::post('web-form-request/sign-in', [WebFormRequestController::class, 'signIn'])
        ->name('web-form-request.sign-in');

    // Route::middleware('guest')
    //     ->post('is-busy-member-email', [IsBusyMemberEmailController::class, 'check'])
    //     ->name('is-busy-member-email');

    // Coupon Controller
    Route::get('coupon/check', [\App\Http\Controllers\Web\CouponController::class, 'check']);
    // get areas list of given city
    Route::get('areas/{city}', [LocationController::class, 'areas']);

    Route::as('gift-card.')
        ->prefix('gift-card')
        ->group(function () {
            Route::get('balance', [\App\Http\Controllers\Web\GiftCardController::class, 'balance']);
            Route::get('discount', [\App\Http\Controllers\Web\GiftCardController::class, 'discount']);
        });
    Route::get('target/GEMSCC/point/getUserPointBalance', [\App\Http\Controllers\Web\GiftCardController::class, 'bb']);
    // Catch all amazon webhook
    Route::post(
        'amazon-webhook',
        [BookingPaymentAmazonController::class, 'webHook']
    );

    Route::get('card-change/{token}', [MemberPaymentScheduleController::class, 'auth'])
        ->name('monthly-payments-card-change');

    // Booking process
    Route::as('booking.')
        ->prefix('checkout')
        ->group(function () {
            Route::post(
                'get-price',
                [\App\Http\Controllers\Web\Booking\Payment\BookingCalculateController::class, 'calculate']
            );

            Route::get('', [BookingStepOneController::class, 'index'])
                ->name('step-1');
            Route::post('', [BookingStepOneController::class, 'store'])
                ->name('step-1-store');

            Route::get(
                '{booking:uuid}/payment',
                [BookingStepTwoController::class, 'index']
            )
                ->middleware([
                    'is-booking-completed',
                    'is-booking-payment-processable',
                ])
                ->name('step-2');
            // Payments
            Route::middleware(BookingPaymentProcessingMiddleware::class)
                ->post('{booking:uuid}/payment', [BookingStepTwoController::class, 'store'])
                ->name('step-2-store');

            Route::as('payment.')
                ->middleware([
                    'is-booking-completed',
                ])
                ->prefix('{booking:uuid}/payment')
                ->group(function () {
                    Route::get('success', [BookingStepTwoController::class, 'paymentSuccess'])
                        ->name('success');
                    Route::get('fail', [BookingStepTwoController::class, 'paymentFail'])
                        ->name('fail');

                    Route::get(
                        'tabby-result/{paymentTransaction:uuid}',
                        [BookingPaymentTabbyController::class, 'index']
                    )
                        ->name('tabby-result');

                    Route::match(
                        ['get', 'post'],
                        'paytabs-redirect/{paymentTransaction:uuid}',
                        [BookingPaymentPaytabsController::class, 'redirect']
                    )
                        ->name('paytabs-redirect');

                    Route::post(
                        'paytabs-response/{paymentTransaction:uuid}',
                        [BookingPaymentPaytabsController::class, 'processPaytabsRequest']
                    )
                        ->name('paytabs-response');

                    Route::get('amazon-request', [BookingPaymentAmazonController::class, 'request'])
                        ->name('amazon-request');
                    Route::post('amazon-response', [BookingPaymentAmazonController::class, 'response'])
                        ->name('amazon-response');
                });

            Route::get(
                '{booking:uuid}/information',
                [BookingStepThreeController::class, 'index']
            )
                ->middleware([
                    'is-booking-completed',
                    'can-booking-information',
                ])
                ->name('step-3');
            Route::post(
                '{booking:uuid}/information',
                [BookingStepThreeController::class, 'store']
            )
                ->middleware('is-booking-completed')
                ->name('step-3-store');

            Route::get(
                '{booking:uuid}/complete',
                [BookingCompleteController::class, 'index']
            )
                ->middleware('is-booking-completed')
                ->name('step-4');
        });

    Route::get('/zoho/oauth2callback', [ZohoController::class, 'oauth2callback']);

    Route::get(
        'mamo-result/{paymentMamoLink}',
        [PaymentMamoController::class, 'index']
    )
        ->name('mamo-result');

    Route::get('robots.txt', function () {
        if (!app()->environment('production')) {
            return response("User-agent: *\nDisallow: /", 200)
                ->header('Content-Type', 'text/plain');
        }
        abort(404);
    });
});
