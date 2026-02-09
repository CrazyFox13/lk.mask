<?php

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyTypeController;
use App\Http\Controllers\VehicleGroupController;
use App\Http\Controllers\VehicleTypeController;
use App\Http\Controllers\PhotoGroupController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FormQuestionController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportTypeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\CompanyNotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\MultipartUploadController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ModeratorController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\UserLogController;
use App\Http\Controllers\FavoriteOrderController;
use App\Http\Controllers\FavoriteUserController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\ReservedNumberController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\GeoCityController;
use App\Http\Controllers\GeoRegionController;
use App\Http\Controllers\OrderAddressController;
use App\Http\Controllers\OrderFilterController;
use App\Http\Controllers\NotificationTypeController;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\PaymentUnitController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UnsubscribeController;
use App\Http\Controllers\AdvertiserController;
use App\Http\Controllers\AdvBannerController;
use App\Http\Controllers\AdvPlaceController;
use App\Http\Controllers\AdvReportController;
use App\Http\Controllers\SubscribedVehicleController;
use App\Http\Controllers\SubscribedGeoController;
use \App\Http\Controllers\EmailNotificationTemplateController;
use \App\Http\Controllers\OrderOfferController;
use App\Http\Controllers\VehicleCategoryController;
use \App\Http\Controllers\SeoController;
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

Route::get('/', function () {
    return response()->json([
        'status' => "success"
    ]);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::post('login/password', [AuthController::class, 'loginByPassword']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('validate-phone-code', [AuthController::class, 'validatePhoneCode']);
    Route::get('check-token', [AuthController::class, 'checkToken']);
    Route::get('verify-email', [AuthController::class, 'validateEmailCode']);
    Route::post('reset-email', [AuthController::class, 'resetEmail']);
    Route::post('auth-by-hash', [AuthController::class, 'authByHash']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('profile', [AuthController::class, 'profile']);
        Route::post('set-password', [AuthController::class, 'setPassword']);
        Route::post('set-device', [AuthController::class, 'setDevice']);
        Route::post('send-email-code', [AuthController::class, 'sendEmailCode']);
        Route::post('avatar', [AuthController::class, 'setAvatar']);
        Route::delete('avatar', [AuthController::class, 'deleteAvatar']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('set-silence', [AuthController::class, 'setSilence']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::delete('delete', [AuthController::class, 'deleteAccount']);
    });
});

Route::resources([
    'companies' => CompanyController::class,
    'orders' => OrderController::class,
    'company-types' => CompanyTypeController::class,
    'vehicle-categories' => VehicleCategoryController::class,
    'vehicle-groups' => VehicleGroupController::class,
    'vehicle-groups.vehicle-types' => VehicleTypeController::class,
    'regions' => RegionController::class,
    'regions.cities' => CityController::class,
    'geo-regions' => GeoRegionController::class,
    'geo-cities' => GeoCityController::class,
    'pages' => PageController::class,
]);

Route::get('orders/{order}', [OrderController::class, 'show'])->withTrashed();
Route::get("adv", [AdvBannerController::class, 'getRandomByTypes']);
Route::post("adv/{advBanner}", [AdvBannerController::class, 'click']);
Route::post("adv-report", [AdvReportController::class, 'report']);
Route::get("adv-report/export", [AdvReportController::class, 'export']);
Route::get('geo-by-ip', [GeoCityController::class, 'findByIp']);
Route::get('materials/{slug}', [PageController::class, 'pageBySlug']);
Route::get('index', [DashboardController::class, 'publicIndex']);

// Публичные маршруты для просмотра компаний (без авторизации)
Route::prefix('companies/{company}')->group(function () {
    Route::get('', [CompanyController::class, 'show'])->withTrashed();
    Route::get('passport', [CompanyController::class, 'passport'])->withTrashed();
});

Route::prefix("seo")->group(function (){
    Route::get('public-urls', [SeoController::class,'publicURLS']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::resources([
        'vehicle-groups.vehicle-types.form-questions' => FormQuestionController::class,
        'companies.photo-groups' => PhotoGroupController::class,
        'companies.photo-groups.photos' => PhotoController::class,
        'companies.employees' => EmployeeController::class,
        'customers.logs' => UserLogController::class,
        'reports' => ReportController::class,
        'report-types' => ReportTypeController::class,
        'recommendations' => RecommendationController::class,
        'company-notifications' => CompanyNotificationController::class,
        'claims' => ClaimController::class,
        'order-filters' => OrderFilterController::class,
        'notification-types' => NotificationTypeController::class,
        'payment-units' => PaymentUnitController::class,
        'awards' => AwardController::class,
        'subscribe-vehicles' => SubscribedVehicleController::class,
        'subscribe-cities' => SubscribedGeoController::class,
    ]);
    Route::middleware('throttle:260,1')->group(function () {
        // Здесь определяются маршруты, для которых применяется ограничение
        Route::resources([
            'chats' => ChatController::class,
            'chats.messages' => MessageController::class,
        ]);
    });

    Route::prefix('customers/{user}')->group(function () {
        Route::get('/', [UserController::class, 'show'])->withTrashed();
        Route::post('favorite', [UserController::class, 'favorite']);
    });

    Route::get('favorite/orders', [FavoriteOrderController::class, 'index']);
    Route::get('favorite/users', [FavoriteUserController::class, 'index']);

    Route::prefix('companies/{company}')->group(function () {
        Route::post('validate', [CompanyController::class, 'validateCompany']);
        Route::post('draft', [CompanyController::class, 'draft']);
        Route::post('moderate', [CompanyController::class, 'moderate']);
        Route::post('approve', [CompanyController::class, 'approve']);
        Route::post('cancel', [CompanyController::class, 'cancel']);
        Route::post('set-reserved-number', [CompanyController::class, 'setReservedNumber']);
        Route::post('awards', [CompanyController::class, 'setAwards']);
        Route::post('set-vehicles', [CompanyController::class, 'setVehicles']);

        Route::post('photo-groups/{photoGroup}/bulk-delete', [PhotoGroupController::class, 'bulkDelete']);

        Route::post("employees/{employee}/delete", [EmployeeController::class, 'sendDeleteConfirmation']);
    });

    Route::prefix('company-notifications')->group(function () {
        Route::post('read-all', [CompanyNotificationController::class, 'readAll']);
        Route::post('{companyNotification}/read', [CompanyNotificationController::class, 'readOne']);
    });

    Route::prefix('orders')->group(function () {
        Route::post('create', [OrderController::class, 'createFullOrder']);
        Route::prefix('validate')->group(function () {
            Route::post('', [OrderController::class, 'store']);
            Route::post('set-dates', [OrderController::class, 'setDates']);
            Route::post('set-addresses', [OrderController::class, 'setAddresses']);
            Route::post('set-budget', [OrderController::class, 'setBudget']);
            Route::post('set-details', [OrderController::class, 'setDetails']);
        });
        Route::prefix('{order}')->group(function () {
            Route::put('', [OrderController::class, 'update']);
            Route::post('set-dates', [OrderController::class, 'setDates']);
            Route::post('set-addresses', [OrderController::class, 'setAddresses']);
            Route::post('set-budget', [OrderController::class, 'setBudget']);
            Route::post('set-details', [OrderController::class, 'setDetails']);
            Route::post('draft', [OrderController::class, 'draft']);
            Route::post('moderate', [OrderController::class, 'moderate']);
            Route::post('on-approval', [OrderController::class, 'onApproval']);
            Route::post('approve', [OrderController::class, 'approve']);
            Route::post('in-progress', [OrderController::class, 'inProgress']);
            Route::post('set-status', [OrderController::class, 'setStatus']);
            Route::post('cancel', [OrderController::class, 'cancel']);
            Route::post('remove', [OrderController::class, 'remove']);
            Route::post('complete', [OrderController::class, 'complete']);
            Route::post('favorite', [OrderController::class, 'favorite']);
            Route::post('chat', [OrderController::class, 'chat']);

            Route::resource("order-offers", OrderOfferController::class);
            Route::prefix('order-offers/{orderOffer}')->group(function () {
                Route::post('accept', [OrderOfferController::class, 'accept']);
                Route::post('undo-accept', [OrderOfferController::class, 'undoAccept']);
                Route::post('decline', [OrderOfferController::class, 'decline']);
                Route::post('revert', [OrderOfferController::class, 'revert']);
                Route::get('print', [OrderOfferController::class, 'print']);
                Route::get('application', [OrderOfferController::class, 'generateApplication']);
                Route::get('invoice', [OrderOfferController::class, 'downloadInvoice']);
                Route::post('upload-signed-document', [OrderOfferController::class, 'uploadSignedDocument']);
                Route::post('set-in-progress', [OrderOfferController::class, 'setInProgress']);
                Route::prefix('documents/{document}')->group(function () {
                    Route::get('download', [OrderOfferController::class, 'downloadDocument']);
                    Route::get('preview', [OrderOfferController::class, 'previewDocument']);
                });
            });
        });
    });

    Route::prefix("chats/{chat}")->group(function () {
        Route::post("mute", [ChatController::class, 'mute']);
        Route::post("block", [ChatController::class, 'block']);
    });

    Route::prefix('reports/{report}')->group(function () {
        Route::get('', [ReportController::class, 'show'])->withTrashed();
        Route::post('draft', [ReportController::class, 'draft']);
        Route::post('moderate', [ReportController::class, 'moderate']);
        Route::post('referee', [ReportController::class, 'referee']);
        Route::post('confirm', [ReportController::class, 'confirm']);
        Route::post('reject', [ReportController::class, 'reject']);
        Route::post('resolve', [ReportController::class, 'resolve']);
        Route::post('cancel', [ReportController::class, 'cancel']);
    });

    Route::prefix('recommendations/{recommendation}')->group(function () {
        Route::post('draft', [RecommendationController::class, 'draft']);
        Route::post('view', [RecommendationController::class, 'view']);
        Route::post('approve', [RecommendationController::class, 'approve']);
        Route::post('cancel', [RecommendationController::class, 'cancel']);
        Route::post('archive', [RecommendationController::class, 'archive']);
    });

    Route::prefix('claims/{claim}')->group(function () {
        Route::post('draft', [ClaimController::class, 'draft']);
        Route::post('view', [ClaimController::class, 'view']);
        Route::post('approve', [ClaimController::class, 'approve']);
        Route::post('cancel', [ClaimController::class, 'cancel']);
    });

    Route::get('push-notifications/{pushNotification}/material', [PushNotificationController::class, 'getMaterial']);

    Route::post('vehicle-groups/{vehicleGroup}/vehicle-types/{vehicleType}/form-questions/order', [FormQuestionController::class, 'order']);

    /* DEPRECATED todo: DELETE AFTER APP UPDATE*/
    Route::get('new-messages-count', [MessageController::class, 'newMessagesCount']);
    Route::get('new-notifications-count', [CompanyNotificationController::class, 'newNotificationsCount']);
    Route::get('new-recommendations-count', [RecommendationController::class, 'newRecommendationsCount']);
    /* /DEPRECATED */

    Route::post('upload', [UploadController::class, 'upload']);
    Route::prefix('upload/multipart')->group(function () {
        Route::post('/', [MultipartUploadController::class, 'createMultipartUpload']);
        Route::post('{uploadId}', [MultipartUploadController::class, 'uploadPart']);
        Route::get('{uploadId}', [MultipartUploadController::class, 'getUploadedParts']);
        Route::get('{uploadId}/{partNumber}', [MultipartUploadController::class, 'signPartUpload']);
        Route::post('{uploadId}/complete', [MultipartUploadController::class, 'completeMultipartUpload']);
        Route::delete('{uploadId}', [MultipartUploadController::class, 'abortMultipartUpload']);
    });

    Route::post("support", [SupportController::class, 'sendOnEmail']);
    Route::post("unsubscribe", [UnsubscribeController::class, 'unsubscribeFromEmail']);

    Route::middleware('moderator')->group(function () {
        Route::resources([
            'users' => UserController::class,
            'push-notifications' => PushNotificationController::class,
            'reserved-numbers' => ReservedNumberController::class,
            'advertisers' => AdvertiserController::class,
            'adv-banners' => AdvBannerController::class,
            'adv-places' => AdvPlaceController::class,
            'email-notification-templates' => EmailNotificationTemplateController::class,
        ]);
        Route::get('users/{user}', [UserController::class, 'show'])->withTrashed();
        Route::post('users/{user}/comment', [UserController::class, 'setComment']);
        Route::post('users/{user}/subscribe-cities', [UserController::class, 'subscribeCities']);
        Route::post('users/{user}/subscribe-vehicles', [UserController::class, 'subscribeVehicles']);
        Route::post('users/{user}/send-email-confirmation', [UserController::class, 'sendEmailConfirmation']);

        Route::get('dashboard', [DashboardController::class, 'getStatData']);
        Route::prefix('push-notifications/{pushNotification}')->group(function () {
            Route::post('material', [PushNotificationController::class, 'setMaterial']);
            Route::get('available-receivers', [PushNotificationController::class, 'availableReceivers']);
            Route::post('attach-all', [PushNotificationController::class, 'attachAll']);
            Route::post('attach/{user}', [PushNotificationController::class, 'attachUser']);
            Route::get('attached-receivers', [PushNotificationController::class, 'attachedReceivers']);
            Route::post('detach-all', [PushNotificationController::class, 'detachAll']);
            Route::post('detach/{user}', [PushNotificationController::class, 'detachUser']);
            Route::post('send', [PushNotificationController::class, 'send']);
            Route::post('pause', [PushNotificationController::class, 'pause']);
            Route::post('resume', [PushNotificationController::class, 'resume']);
            Route::post('cancel', [PushNotificationController::class, 'cancel']);
            Route::post('copy', [PushNotificationController::class, 'copy']);
            Route::get('users', [PushNotificationController::class, 'users']);
        });

        Route::prefix('chats/{chat}')->group(function () {
            Route::post('enter', [ChatController::class, 'enter']);
        });

        Route::prefix('reports/{report}')->group(function () {
            Route::post('conclusion', [ReportController::class, 'setConclusion']);
        });

        Route::post('orders/multiple-delete', [OrderController::class, 'multipleDelete']);
        Route::post('orders/multiple-moderation', [OrderController::class, 'multipleModeration']);
    });

    Route::prefix("badges")->group(function () {
        Route::get('total', [BadgeController::class, 'totalCount'])->middleware('throttle:260,1');
        Route::get('reports', [BadgeController::class, 'reportMessagesCount']);
        Route::get('notifications', [BadgeController::class, 'notificationsCount']);
        Route::get('recommendations', [BadgeController::class, 'recommendationsCount']);

        Route::middleware('moderator')->prefix('admin')->group(function () {
            Route::get('orders', [BadgeController::class, 'moderationOrders']);
            Route::get('reports', [BadgeController::class, 'refereeReports']);
            Route::get('claims', [BadgeController::class, 'draftClaims']);
            Route::get('recommendations', [BadgeController::class, 'draftRecommendations']);
            Route::get('companies', [BadgeController::class, 'moderationCompanies']);
        });
    });

    Route::get('company-by-inn', [CompanyController::class, 'finDataByINN']);
    Route::get('validate-inn', [CompanyController::class, 'validateINN']);
    Route::get('address-from-string', [OrderAddressController::class, 'addressFromString']);
    Route::get('address-from-geo', [OrderAddressController::class, 'addressFromGeo']);

    Route::post('/broadcasting/auth', [\App\Http\Controllers\BroadcastController::class, 'auth']);
    Route::post('/broadcasting/auth-presence', [\App\Http\Controllers\BroadcastController::class, 'authPresence']);

    Route::middleware('admin')->group(function () {
        Route::resource("moderators", ModeratorController::class);
        Route::post("moderators/{moderator}/reset-password", [ModeratorController::class, 'resetPassword']);

        Route::post("pages/move", [PageController::class, 'move']);

        Route::post("reports/employee-order", [OrderController::class, "employeeReport"]);
    });
});
