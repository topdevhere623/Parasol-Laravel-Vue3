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
use App\Http\Controllers\Api\Crm\KanbanController;
use App\Http\Controllers\Api\Crm\LeadStatistics\TeamPerformanceLeadStatisticsController;
use App\Http\Controllers\Api\Crm\MemberController;
use App\Http\Controllers\Api\Crm\MembershipSourceSortingController;
use App\Http\Controllers\Api\Crm\PaymentController;
use App\Http\Controllers\Api\Crm\ReferralController;
use App\Http\Controllers\Api\Crm\SalesQuoteController;
use App\Http\Controllers\Api\Crm\SettingsController;
use App\Http\Middleware\AbleToCheckinMiddleware;
use ParasolCRMV2\Http\Controllers\MenuController;

Route::group(['prefix' => '/', 'namespace' => 'Api\Crm', 'as' => 'apiCrm.v2.'], function () {
    // CRM API
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('forgot-password', [AuthController::class, 'passwordResetRequest']);
        Route::post('reset-password', [AuthController::class, 'setPasswordRequest']);
    });

    Route::group(['middleware' => ['auth:backoffice_user']], function () {
        Route::get('menu', [MenuController::class, 'menu']);
        Route::group(['prefix' => 'auth'], function () {
            Route::get('user', 'AuthController@user');
            Route::post('logout', 'AuthController@logout');
            Route::post('new-password', [AuthController::class, 'changePasswordRequest']);
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

        Route::prefix('lead')->group(function () {
            Route::get('form', [\App\Http\Controllers\Api\Crm\LeadController::class, 'edit']);
            Route::post('create', [\App\Http\Controllers\Api\Crm\LeadController::class, 'create']);
            Route::post('check-duplicate', [\App\Http\Controllers\Api\Crm\LeadController::class, 'checkDuplicates']);
            Route::get('{lead}', [\App\Http\Controllers\Api\Crm\LeadController::class, 'view']);
            Route::get('{lead}/form', [\App\Http\Controllers\Api\Crm\LeadController::class, 'edit']);
            Route::patch('{lead}', [\App\Http\Controllers\Api\Crm\LeadController::class, 'update']);
            Route::delete('{lead}', [\App\Http\Controllers\Api\Crm\LeadController::class, 'destroy']);
            Route::post('{lead}/delete-note', [\App\Http\Controllers\Api\Crm\LeadController::class, 'deleteNote']);
            Route::post(
                '{lead}/delete-activity',
                [\App\Http\Controllers\Api\Crm\LeadController::class, 'deleteActivity']
            );
            Route::apiResource('{lead}/comments', \App\Http\Controllers\Api\Crm\CrmCommentController::class)
                ->only(['store', 'update', 'destroy']);
            Route::post(
                '{lead}/comments/{comment}/pin',
                [\App\Http\Controllers\Api\Crm\CrmCommentController::class, 'togglePin']
            );
        });
        Route::prefix('lead-statistics')->group(function () {
            Route::get(
                'team-performance',
                [TeamPerformanceLeadStatisticsController::class, 'index']
            );
            Route::get(
                'team-performance/filters',
                [TeamPerformanceLeadStatisticsController::class, 'filtersIndex']
            );
            Route::get(
                'company-performance',
                [
                    \App\Http\Controllers\Api\Crm\LeadStatistics\CompanyPerformanceLeadStatisticsController::class,
                    'index',
                ]
            );
            Route::get(
                'company-performance/filters',
                [
                    \App\Http\Controllers\Api\Crm\LeadStatistics\CompanyPerformanceLeadStatisticsController::class,
                    'filtersIndex',
                ]
            );
        });

        Route::group(['prefix' => 'kanban'], function () {
            // kanban routes start
            Route::match(['put', 'patch'], 'move-card/{id}', [KanbanController::class, 'moveCard']);
            Route::get('list', [KanbanController::class, 'index']);
            Route::get('steps', [KanbanController::class, 'steps']);
        });

        Route::prefix('crm-activities')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Crm\CrmActivityController::class, 'index']);
        });

        Route::prefix('attachments')->group(function () {
            Route::delete('{attachment}', [\App\Http\Controllers\Api\Crm\CrmAttachmentController::class, 'destroy']);
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Crm\NotificationController::class, 'index']);
            Route::post(
                '/mark-as-read',
                [\App\Http\Controllers\Api\Crm\NotificationController::class, 'markNotification']
            );
        });

        Route::post('member/generate-renewal-link', [MemberController::class, 'generateRenewalLink']);
    });
});
