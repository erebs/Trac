<?php



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


/**********************  User *********************************************** */

Route::post('/register', [ApiController::class, 'register']);
Route::post('/login', [ApiController::class, 'userLogin']);
Route::post('/register_validation', [ApiController::class, 'registerValidation']);
Route::post('/user_verification', [ApiController::class, 'userVerification']);
Route::post('/update_profile', [ApiController::class, 'updateProfile']);
Route::post('/reset_password', [ApiController::class, 'resetPassword']);
Route::post('/change_password', [ApiController::class, 'changePassword']);

/*********************** Shop ************************************************ */

Route::post('/shop_register', [ApiController::class, 'shopRegister']);
Route::post('/shop_login', [ApiController::class, 'shopLogin']);
Route::post('/shops_by_user', [ApiController::class, 'shopsByUser']);
Route::post('/shop_status_update', [ApiController::class, 'shopStatusUpdate']);
Route::post('/shop_update', [ApiController::class, 'shopUpdate']);

/********************** Fraud ************************************************ */

Route::post('/fraud_create', [ApiController::class, 'fraudCreate']);
Route::post('/fraud_images_upload', [ApiController::class, 'fraudImagesUpload']);
Route::post('/fraud_images', [ApiController::class, 'fraudImages']);
Route::post('/search_fraud', [ApiController::class, 'searchFraud']);
Route::post('/fruad_by_shop', [ApiController::class, 'fraudsByShop']);

/*******************  Transaction  ******************************************* */

Route::post('/transaction_register', [ApiController::class, 'transactionRegister']);
Route::post('/get_transaction', [ApiController::class, 'getTransaction']);

/*******************  Subscription  ****************************************** */

Route::post('/subscription_register', [ApiController::class, 'subscriptionRegister']);
Route::post('/subscription', [ApiController::class, 'subscription']);

/************************ change  User password ************************************************ */

Route::post('change-password', [ApiController::class, 'changePassword']);


/************************* Paytm Gateway **************************** */

Route::post('initiate_transaction', [ApiController::class, 'initiateTransaction']);
Route::post('transaction_status', [ApiController::class, 'transactionStatus']);
