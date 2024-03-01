<?php
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use App\Http\Controllers\Api\Crm\AuthController;
use App\Http\Controllers\Api\Crm\BookingController;
use App\Http\Controllers\Api\Crm\CheckinController;
use App\Http\Controllers\Api\Crm\ClubSortingController;
use App\Http\Controllers\Api\Crm\DuplicateController;
use App\Http\Controllers\Api\Crm\MemberController;
use App\Http\Controllers\Api\Crm\MembershipSourceSortingController;
use App\Http\Controllers\Api\Crm\PaymentController;
use App\Http\Controllers\Api\Crm\ReferralController;
use App\Http\Controllers\Api\Crm\SalesQuoteController;
use App\Http\Controllers\Api\Crm\SettingsController;
use App\Http\Middleware\AbleToCheckinMiddleware;

Route::group(['prefix' => '/', 'namespace' => 'Api\Crm', 'as' => 'apiCrm.'], function () {
    // CRM API
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('forgot-password', [AuthController::class, 'passwordResetRequest']);
        Route::post('reset-password', [AuthController::class, 'setPasswordRequest']);
    });

    Route::group(['middleware' => ['auth:backoffice_user']], function () {
        Route::get('menu', 'MenuController@menu');
        Route::group(['prefix' => 'auth'], function () {
            Route::get('user', 'AuthController@user');
            Route::post('logout', 'AuthController@logout');
        });

        Route::get('auth-as-member-with-token/{memberId}', [MemberController::class, 'authAsMemberWithToken']);
        Route::post('send-email-to-member/', [MemberController::class, 'sendEmailToMember']);
        Route::apiResource('logs', 'ActivityLogController')
            ->only([
                'index',
                'show',
            ]);

        Route::apiResource('document', 'DocumentController')
            ->only([
                'store',
                'update',
                'destroy',
            ]);

        Route::patch('duplicate/{name}', [DuplicateController::class, 'store']);
        Route::get('clubs-sorting/{program}', [ClubSortingController::class, 'index']);
        Route::post('clubs-sorting', [ClubSortingController::class, 'update']);
        Route::get('membership-source-sorting', [MembershipSourceSortingController::class, 'index']);
        Route::post('membership-source-sorting', [MembershipSourceSortingController::class, 'update']);
        Route::get('referral/getMemberShotData', [ReferralController::class, 'getMemberShotData']);
        Route::get('referral/getMemberFullData', [ReferralController::class, 'getMemberFullData']);
        Route::get('booking/{booking}/view', [BookingController::class, 'view']);

        Route::prefix('checkin')->group(function () {
            Route::patch('member/check-out', [CheckinController::class, 'checkout']);
            Route::get('member/find', [CheckinController::class, 'findMember']);
            Route::get('filter/club-options', [CheckinController::class, 'filterClubsOptions']);
            Route::middleware(AbleToCheckinMiddleware::class)->group(function () {
                Route::post('member/check-in', [CheckinController::class, 'checkin']);
                Route::post('member/check-in-class', [CheckinController::class, 'checkinClass']);
                Route::post('member/paid-guest-fee', [CheckinController::class, 'paidGuestFee']);
                Route::post('member/turned-away', [CheckinController::class, 'turnedAway']);
            });
        });

        Route::prefix('payment')->group(function () {
            Route::get(
                'refund-available/{payment}',
                [PaymentController::class, 'refundAvailable']
            );
            Route::post('refund/{payment}', [PaymentController::class, 'refund']);
        });

        Route::get('setting/form', [SettingsController::class, 'form']);
        Route::post('setting', [SettingsController::class, 'update']);

        // Locations
        Route::get('location/getCountries', 'LocationController@getCountries');
        Route::get('membership/durations', 'MembershipController@getDurations');
        // Route::get('locations/getCities', 'LocationController@getCities');
        // Route::get('locations/getAreas', 'LocationController@getAreas');

        Route::prefix('sales-quotes')->group(function () {
            Route::post('card', [SalesQuoteController::class, 'getCard']);
            Route::get('pdf/{salesQuote}', [SalesQuoteController::class, 'getPdf']);
        });

        Route::post('member/generate-renewal-link', [MemberController::class, 'generateRenewalLink']);
    });
});
