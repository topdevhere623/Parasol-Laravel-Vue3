<?php
/*
|--------------------------------------------------------------------------
| Web API Routes
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\AutocompleteController;
use App\Http\Controllers\Api\HSBCController;
use App\Http\Controllers\Api\Lead\LeadFacebookWebhookHandler;
use App\Http\Controllers\Api\Lead\LeadUnbounceWebhookHandler;
use App\Http\Controllers\Api\Lead\LeadZapierWebhookHandler;
use App\Http\Controllers\Api\MemberPortal\AuthController as MemberPortalAuthController;
use App\Http\Controllers\Api\MemberPortal\ClubController;
use App\Http\Controllers\Api\MemberPortal\OfferController;
use App\Http\Controllers\Api\MemberPortal\PaymentController;
use App\Http\Controllers\Api\MemberPortal\ReferralController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\Program\AuthController;
use App\Http\Controllers\Web\FileController;

Route::group(['prefix' => '/', 'namespace' => 'Api'], function () {
    // Passkit updates webhook handler
    Route::match(
        ['PUT', 'POST', 'DELETE'],
        'passkit-webhook/{api_key}',
        \App\Http\Controllers\Api\PassKitWebhookHandler::class
    );
    Route::post(
        'tabby-webhook',
        \App\Http\Controllers\Api\TabbyWebhookHandler::class
    );
    // lead.assigned event handler
    Route::post(
        'nocrm-webhook',
        \App\Http\Controllers\Api\NocrmWebhookHandler::class
    )->name('nocrm-webhook');

    // unbounce form sent event handler
    Route::post('lead/unbounce-webhook/{secureToken}', LeadUnbounceWebhookHandler::class);
    // zapier webhook handler
    Route::post('lead/zapier-webhook/{secureToken}', LeadZapierWebhookHandler::class);
    // facebook webhook handler
    Route::match(
        ['get', 'post'],
        'facebook-webhook/{secureToken}',
        LeadFacebookWebhookHandler::class
    );

    Route::post('newsletter-subscribe', [NewsletterController::class, 'subscribe']);

    // Gems app request handler
    Route::get('gems', [\App\Http\Controllers\Api\GemsController::class, '__invoke']);

    Route::post('/hsbc-bin/check', [HSBCController::class, 'hsbcBinCheck']);

    Route::post('/autocomplete-corporate', [AutocompleteController::class, 'corporate'])
        ->name('autocomplete-corporate');

    // LEGACY PROGRAM ROUTES
    Route::group(['namespace' => 'Program'], function () {
        // Auth for program
        Route::post('login', [AuthController::class, 'login']);

        // File upload
        Route::post('photo/upload', [FileController::class, 'uploadPhotos']);

        Route::group(['middleware' => ['auth:programs']], function () {
            // Auth for program
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh-token', [AuthController::class, 'refreshToken']);

            // TODO:: must be into middleware' => ['auth:programs]
            // Auth for admin = back office users
            Route::post('auth/login', [\App\Http\Controllers\Api\Program\AuthController::class, 'login']);
            Route::post(
                'auth/refresh-token',
                [\App\Http\Controllers\Api\Program\AuthController::class, 'refresh_token']
            );

            // Club
            Route::get('club/list', 'ClubController@index');
            Route::get('club/list/{id}', 'ClubController@show');

            // Members
            Route::resource('members', 'MemberController');
        });
    });

    Route::group(['prefix' => 'program/v1', 'namespace' => 'v1\Program'], function () {
        if (app()->isLocal()) {
            Route::post('webhook-test', function (Illuminate\Http\Request $request) {
                info(json_encode($request->toArray()));
            })->name('program-api.webhook-test');
        }

        Route::group(['middleware' => ['auth:program-api']], function () {
            // Bookings
            Route::post('bookings/remote', [\App\Http\Controllers\Api\v1\Program\BookingController::class, 'remote']);

            // Members
            Route::post('members/auth', [\App\Http\Controllers\Api\v1\Program\MemberController::class, 'auth']);

            // Clubs
            Route::get('clubs', [\App\Http\Controllers\Api\v1\Program\ClubController::class, 'index']);
            Route::get('clubs/{uuid}', [\App\Http\Controllers\Api\v1\Program\ClubController::class, 'show'])
                ->whereUuid('uuid');
            Route::put('clubs/{uuid}', [\App\Http\Controllers\Api\v1\Program\ClubController::class, 'show']);

            // Members
            Route::get('members', [\App\Http\Controllers\Api\v1\Program\MemberController::class, 'index']);
            Route::get(
                'members/{membership_number}',
                [\App\Http\Controllers\Api\v1\Program\MemberController::class, 'show']
            );

            // Offers
            Route::get('offers', [\App\Http\Controllers\Api\v1\Program\OfferController::class, 'index']);
            Route::get('offers/types', [\App\Http\Controllers\Api\v1\Program\OfferController::class, 'offerTypes']);
            Route::get(
                'offers/emirates',
                [\App\Http\Controllers\Api\v1\Program\OfferController::class, 'offerEmirates']
            );
            Route::get('offers/{uuid}', [\App\Http\Controllers\Api\v1\Program\OfferController::class, 'show'])
                ->whereUuid('uuid');
        });
    });

    // MEMBER PORTAL
    Route::group(['prefix' => 'member-portal', 'namespace' => 'MemberPortal'], function () {
        Route::group(['prefix' => 'auth'], function () {
            // Auth
            Route::post('login', [MemberPortalAuthController::class, 'login']);
            Route::post('refresh-token', [MemberPortalAuthController::class, 'refreshToken']);

            // Password reset
            Route::post('forgot-password', [MemberPortalAuthController::class, 'passwordResetRequest']);
            Route::post('reset-password', [MemberPortalAuthController::class, 'setPasswordRequest']);
            Route::post('create-password', [MemberPortalAuthController::class, 'createPassword']);

            Route::group(['middleware' => ['auth:api']], function () {
                Route::get('user', [MemberPortalAuthController::class, 'user']);
                Route::post('logout', [MemberPortalAuthController::class, 'logout']);
                Route::post('new-password', [MemberPortalAuthController::class, 'changePasswordRequest']);
            });
        });

        Route::group(['middleware' => ['auth:api']], function () {
            // Clubs
            Route::get('my-clubs', [ClubController::class, 'index']);
            Route::get('my-clubs/{slug}', [ClubController::class, 'show']);
            Route::get('all-clubs', [ClubController::class, 'allClubs']);
            Route::post('add-to-favorites', [ClubController::class, 'addToFavorites']);
            Route::post('remove-from-favorites', [ClubController::class, 'removeFromFavorites']);

            // Member profile
            Route::group(['prefix' => 'payments'], function () {
                Route::get('', [PaymentController::class, 'index']);
                Route::get('change-card', [PaymentController::class, 'getCardChangeLink']);
                Route::post('attach-card', [PaymentController::class, 'attachCard']);
            });

            // Referrals
            Route::group(['prefix' => 'referrals'], function () {
                Route::resource('/', ReferralController::class)
                    ->only(['index', 'store']);
                Route::group(['prefix' => 'rewards'], function () {
                    Route::put('{uuid}', [ReferralController::class, 'chooseReward']);
                    Route::get('options', [ReferralController::class, 'rewardOptions']);
                    Route::get('clubs', [ReferralController::class, 'rewardClubs']);
                });
            });
            // About Adv + membership
            Route::resource('about-membership', 'AboutMembershipController')
                ->only(['index', 'show']);

            // Offers
            Route::resource('offers', 'OfferController')
                ->only(['index', 'show']);
            Route::get('offer-types', [OfferController::class, 'offerTypes']);
            Route::get('offer-emirates', [OfferController::class, 'offerEmirates']);
        });
    });
});
